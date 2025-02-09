<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use function count;

use InvalidArgumentException;

use function is_string;

use Ricklab\Location\Calculator\CalculatorRegistry;
use Ricklab\Location\Calculator\DefaultDistanceCalculator;
use Ricklab\Location\Calculator\DistanceCalculator;
use Ricklab\Location\Converter\DegreesMinutesSeconds;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Ellipsoid\DefaultEllipsoid;
use Ricklab\Location\Exception\BoundBoxRangeException;
use Ricklab\Location\Geometry\Traits\TransformationTrait;

use function round;
use function sprintf;

final class Point implements GeometryInterface
{
    use TransformationTrait;

    public const MAX_LATITUDE = 90;
    public const MIN_LATITUDE = -90;
    public const MAX_LONGITUDE = 180;
    public const MIN_LONGITUDE = -180;

    /** @readonly  */
    private float $longitude;

    /** @readonly  */
    private float $latitude;

    /** @readonly  */
    private string $longitudeString;

    /** @readonly  */
    private string $latitudeString;

    public static function fromArray(array $geometries): self
    {
        if (2 !== $length = count($geometries)) {
            throw new InvalidArgumentException(sprintf('Must be an array consisting of exactly 2 elements, %d passed', $length));
        }

        return new self((float) $geometries[0], (float) $geometries[1]);
    }

    /**
     * Create a new point from 2 DegreesMinutesSeconds objects. The actual order they are passed in does not matter as
     * the axis can be determined from the direction.
     */
    public static function fromDms(DegreesMinutesSeconds $lat, DegreesMinutesSeconds $lon): self
    {
        $longitude = null;
        $latitude = null;

        foreach ([$lat, $lon] as $dms) {
            if (DegreesMinutesSeconds::AXIS_LONGITUDE === $dms->getAxis()) {
                $longitude = $dms;

                continue;
            }

            if (DegreesMinutesSeconds::AXIS_LATITUDE === $dms->getAxis()) {
                $latitude = $dms;
            }
        }

        if (!$longitude instanceof DegreesMinutesSeconds) {
            throw new InvalidArgumentException('Longitude coordinates missing');
        }

        if (!$latitude instanceof DegreesMinutesSeconds) {
            throw new InvalidArgumentException('Latitude coordinates missing');
        }

        return new self($longitude->toDecimal(), $latitude->toDecimal());
    }

    public static function validLongitude(float $long): bool
    {
        return $long <= self::MAX_LONGITUDE && $long >= self::MIN_LONGITUDE && !is_nan($long);
    }

    public static function validateLatitude(float $lat): bool
    {
        return $lat <= self::MAX_LATITUDE && $lat >= self::MIN_LATITUDE && !is_nan($lat);
    }

    /**
     * Create a new Point from Longitude and latitude.
     *
     * Usage: new Point(latitude, longitude);
     * or new Point([longitude, latitude]);
     *
     * @param float|numeric-string $long Longitude coordinates
     * @param float|numeric-string $lat  Latitude coordinates
     */
    public function __construct(float|string $long, float|string $lat)
    {
        if (is_string($lat)) {
            /** @psalm-suppress DocblockTypeContradiction for safety */
            if (!is_numeric($lat)) {
                throw new InvalidArgumentException('latitude must be a valid number between -90 and 90.');
            }

            $this->latitudeString = $lat;
            $this->latitude = (float) $lat;
        } else {
            $this->latitudeString = (string) $lat;
            $this->latitude = $lat;
        }

        if (is_string($long)) {
            /** @psalm-suppress DocblockTypeContradiction for safety */
            if (!is_numeric($long)) {
                throw new InvalidArgumentException('longitude must be a valid number between -180 and 180.');
            }

            $this->longitudeString = $long;
            $this->longitude = (float) $long;
        } else {
            $this->longitudeString = (string) $long;
            $this->longitude = $long;
        }

        if (!self::validateLatitude($this->latitude)) {
            throw new InvalidArgumentException('latitude must be a valid number between -90 and 90.');
        }

        if (!self::validLongitude($this->longitude)) {
            throw new InvalidArgumentException('longitude must be a valid number between -180 and 180.');
        }
    }

    public function getLatitudeInDms(): DegreesMinutesSeconds
    {
        return DegreesMinutesSeconds::fromDecimal($this->latitude, DegreesMinutesSeconds::AXIS_LATITUDE);
    }

    public function getLongitudeInDms(): DegreesMinutesSeconds
    {
        return DegreesMinutesSeconds::fromDecimal($this->longitude, DegreesMinutesSeconds::AXIS_LONGITUDE);
    }

    public function wktFormat(): string
    {
        return $this->toString(' ');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toString(' ');
    }

    public function toString(string $separator): string
    {
        return $this->longitudeString.$separator.$this->latitudeString;
    }

    /**
     * The latitude.
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLatitudeAsString(): string
    {
        return $this->latitudeString;
    }

    /**
     * Find distance to another point.
     *
     * @param string $unit Defaults to meters
     *
     * @psalm-param UnitConverter::UNIT_* $unit Defaults to meters
     *
     * @param DistanceCalculator|null $calculator The calculator that is used for calculating the distance. If null, uses DefaultDistanceCalculator.
     *
     * @return float the distance
     */
    public function distanceTo(
        Point $point2,
        string $unit = UnitConverter::UNIT_METERS,
        ?DistanceCalculator $calculator = null,
    ): float {
        if (null === $calculator) {
            $calculator = CalculatorRegistry::getDistanceCalculator();
        }

        $result = $calculator->calculateDistance($this, $point2, DefaultEllipsoid::get());

        return UnitConverter::convert($result, UnitConverter::UNIT_METERS, $unit);
    }

    /**
     * Find a location a distance and bearing from this one.
     *
     * @param float  $distance distance to other point
     * @param float  $bearing  initial bearing to other point
     * @param string $unit     The unit the distance is in
     *
     * @psalm-param UnitConverter::UNIT_* $unit The unit the distance is in
     */
    public function getRelativePoint(float $distance, float $bearing, string $unit = UnitConverter::UNIT_METERS): Point
    {
        $rad = DefaultEllipsoid::get()->radius($unit);
        $lat1 = $this->latitudeToRad();
        $lon1 = $this->longitudeToRad();
        $bearing = deg2rad($bearing);

        $lat2 = sin($lat1) * cos($distance / $rad) +
            cos($lat1) * sin($distance / $rad) * cos($bearing);
        $lat2 = asin($lat2);

        $lon2y = sin($bearing) * sin($distance / $rad) * cos($lat1);
        $lon2x = cos($distance / $rad) - sin($lat1) * sin($lat2);
        $lon2 = $lon1 + atan2($lon2y, $lon2x);

        return new self(rad2deg($lon2), rad2deg($lat2));
    }

    /**
     * Get the latitude in Rads.
     *
     * @return float Latitude in Rads
     */
    public function latitudeToRad(): float
    {
        return deg2rad($this->latitude);
    }

    /**
     * Get the longitude in Rads.
     *
     * @return float Longitude in Rads
     */
    public function longitudeToRad(): float
    {
        return deg2rad($this->longitude);
    }

    /**
     * Get the initial bearing from this Point to another.
     *
     * @return float bearing
     */
    public function initialBearingTo(Point $point2): float
    {
        return CalculatorRegistry::getBearingCalculator()->calculateInitialBearing($this, $point2);
    }

    /**
     * Get the final bearing from this Point to another.
     *
     * @return float bearing
     */
    public function finalBearingTo(Point $point2): float
    {
        return CalculatorRegistry::getBearingCalculator()->calculateFinalBearing($this, $point2);
    }

    /**
     * The point as an array in the order of longitude, latitude.
     *
     * @return array{0: float, 1: float}
     */
    public function toArray(): array
    {
        return $this->getCoordinates();
    }

    /**
     * The longitude.
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getLongitudeAsString(): string
    {
        return $this->longitudeString;
    }

    /**
     * Finds the mid-point between two points.
     *
     * @return self the mid-point
     */
    public function getMidpoint(Point $point, ?DistanceCalculator $calculator = null): self
    {
        return $this->getFractionAlongLineTo($point, 0.5, $calculator);
    }

    /**
     * Returns the point which is a fraction along the line between 0 and 1.
     *
     * @param float $fraction between 0 and 1
     *
     * @throw \InvalidArgumentException
     */
    public function getFractionAlongLineTo(Point $point, float $fraction, ?DistanceCalculator $calculator = null): self
    {
        return CalculatorRegistry::getFractionAlongLineCalculator()->calculateFractionAlongLine(
            $this,
            $point,
            $fraction,
            $calculator ?? CalculatorRegistry::getDistanceCalculator(),
            DefaultEllipsoid::get()
        );
    }

    /**
     * Create a line between this point and another point.
     */
    public function lineTo(Point $point): LineString
    {
        return new LineString([$this, $point]);
    }

    /**
     * @throws BoundBoxRangeException
     *
     * @psalm-param UnitConverter::UNIT_* $unit
     */
    public function getBBoxByRadius(float $radius, string $unit = UnitConverter::UNIT_METERS): BoundingBox
    {
        return BoundingBox::fromCenter($this, $radius, $unit);
    }

    /**
     * The coordinates in the order of [longitude, latitude].
     *
     * @return array{0: float, 1: float}
     */
    public function getCoordinates(): array
    {
        return [$this->longitude, $this->latitude];
    }

    /**
     * This point in an array.
     *
     * @return list<Point>
     */
    public function getPoints(): array
    {
        return [$this];
    }

    public function equals(GeometryInterface $geometry): bool
    {
        return $this === $geometry
            || ($geometry instanceof self
            && $geometry->latitude === $this->latitude
            && $geometry->longitude === $this->longitude);
    }

    public function getGeoHash(int $resolution = 12): Geohash
    {
        return Geohash::fromPoint($this, $resolution);
    }

    public function round(int $precision): Point
    {
        $point = clone $this;
        $point->latitude = round($this->latitude, $precision);
        $point->latitudeString = (string) $point->latitude;
        $point->longitude = round($this->longitude, $precision);
        $point->longitudeString = (string) $point->longitude;

        return $point;
    }

    public function getBBox(): BoundingBox
    {
        return BoundingBox::fromGeometry($this);
    }

    public function getChildren(): array
    {
        return [];
    }
}
