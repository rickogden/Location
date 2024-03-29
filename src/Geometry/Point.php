<?php

declare(strict_types=1);

/**
 * Description of Point.
 *
 * @author rick
 */

namespace Ricklab\Location\Geometry;

use function count;

use InvalidArgumentException;
use Ricklab\Location\Calculator\BearingCalculator;
use Ricklab\Location\Calculator\DefaultDistanceCalculator;
use Ricklab\Location\Calculator\DistanceCalculator;
use Ricklab\Location\Calculator\FractionAlongLineCalculator;
use Ricklab\Location\Converter\DegreesMinutesSeconds;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Ellipsoid\DefaultEllipsoid;
use Ricklab\Location\Exception\BoundBoxRangeException;
use Ricklab\Location\Geometry\Traits\TransformationTrait;
use Ricklab\Location\Location;

use function round;

class Point implements GeometryInterface
{
    use TransformationTrait;

    public const MAX_LATITUDE = 90;
    public const MIN_LATITUDE = -90;
    public const MAX_LONGITUDE = 180;
    public const MIN_LONGITUDE = -180;

    protected float $longitude;
    protected float $latitude;

    public static function getWktType(): string
    {
        return 'POINT';
    }

    public static function getGeoJsonType(): string
    {
        return 'Point';
    }

    public static function fromArray(array $geometries): self
    {
        if (2 !== $length = count($geometries)) {
            throw new InvalidArgumentException(sprintf('Must be an array consisting of exactly 2 elements, %d passed', $length));
        }

        return new self($geometries[0], $geometries[1]);
    }

    /**
     * Create a new point from 2 DegreesMinutesSeconds objects. The actual order they are passed in does not matter as
     * the axis can be determined from the direction.
     */
    public static function fromDms(DegreesMinutesSeconds $lat, DegreesMinutesSeconds $lon): self
    {
        foreach ([$lat, $lon] as $dms) {
            if (DegreesMinutesSeconds::AXIS_LONGITUDE === $dms->getAxis()) {
                $longitude = $dms;

                continue;
            }

            if (DegreesMinutesSeconds::AXIS_LATITUDE === $dms->getAxis()) {
                $latitude = $dms;
            }
        }

        if (!isset($longitude)) {
            throw new InvalidArgumentException('Longitude coordinates missing');
        }

        if (!isset($latitude)) {
            throw new InvalidArgumentException('Latitude coordinates missing');
        }

        return new self($longitude->toDecimal(), $latitude->toDecimal());
    }

    /**
     * Create a new Point from Longitude and latitude.
     *
     * Usage: new Point(latitude, longitude);
     * or new Point([longitude, latitude]);
     *
     * @param float $long Longitude coordinates
     * @param float $lat  Latitude coordinates
     */
    public function __construct(float $long, float $lat)
    {
        $this->setLatitude($lat);
        $this->setLongitude($long);
    }

    public function getLatitudeInDms(): DegreesMinutesSeconds
    {
        return DegreesMinutesSeconds::fromDecimal($this->latitude, DegreesMinutesSeconds::AXIS_LATITUDE);
    }

    public function getLongitudeInDms(): DegreesMinutesSeconds
    {
        return DegreesMinutesSeconds::fromDecimal($this->longitude, DegreesMinutesSeconds::AXIS_LONGITUDE);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->longitude.' '.$this->latitude;
    }

    /**
     * The latitude.
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * Find distance to another point.
     *
     * @param string                  $unit       Defaults to meters
     * @param DistanceCalculator|null $calculator The calculator that is used for calculating the distance. If null, uses DefaultDistanceCalculator.
     *
     * @return float the distance
     */
    public function distanceTo(
        Point $point2,
        string $unit = UnitConverter::UNIT_METERS,
        ?DistanceCalculator $calculator = null
    ): float {
        if (null === $calculator) {
            $result = DefaultDistanceCalculator::calculate($this, $point2, DefaultEllipsoid::get());
        } else {
            $result = $calculator::calculate($this, $point2, DefaultEllipsoid::get());
        }

        return UnitConverter::convert($result, UnitConverter::UNIT_METERS, $unit);
    }

    /**
     * Find a location a distance and bearing from this one.
     *
     * @param float  $distance distance to other point
     * @param float  $bearing  initial bearing to other point
     * @param string $unit     The unit the distance is in
     */
    public function getRelativePoint(float $distance, float $bearing, string $unit = UnitConverter::UNIT_METERS): Point
    {
        $rad = DefaultEllipsoid::get()::radius($unit);
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
        return BearingCalculator::calculateInitialBearing($this, $point2);
    }

    /**
     * Get the final bearing from this Point to another.
     *
     * @return float bearing
     */
    public function finalBearingTo(Point $point2): float
    {
        return BearingCalculator::calculateFinalBearing($this, $point2);
    }

    /**
     * The point as an array in the order of longitude, latitude.
     *
     * @return float[]
     */
    public function toArray(): array
    {
        return [$this->longitude, $this->latitude];
    }

    /**
     * The longitude.
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Finds the mid point between two points.
     *
     * @return Point the mid point
     */
    public function getMidpoint(Point $point, ?DistanceCalculator $calculator = null): Point
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
        return FractionAlongLineCalculator::calculate(
            $this,
            $point,
            $fraction,
            $calculator ?? DefaultDistanceCalculator::getDefaultCalculator(),
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
     */
    public function getBBoxByRadius(float $radius, string $unit = UnitConverter::UNIT_METERS): BoundingBox
    {
        return BoundingBox::fromCenter($this, $radius, $unit);
    }

    /**
     * Converts point to Well-Known Text.
     */
    public function toWkt(): string
    {
        return sprintf('%s(%s)', self::getWktType(), (string) $this);
    }

    /**
     * The coordinates in the order of [longitude, latitude].
     *
     * @return float[]
     */
    public function getCoordinates(): array
    {
        return [$this->longitude, $this->latitude];
    }

    /**
     * This point in an array.
     *
     * @return Point[]
     */
    public function getPoints(): array
    {
        return [$this];
    }

    private function setLatitude(float $lat): void
    {
        if ($lat > self::MAX_LATITUDE || $lat < self::MIN_LATITUDE || is_nan($lat)) {
            throw new InvalidArgumentException('latitude must be a valid number between -90 and 90.');
        }

        $this->latitude = $lat;
    }

    private function setLongitude(float $long): void
    {
        if ($long > self::MAX_LONGITUDE || $long < self::MIN_LONGITUDE || is_nan($long)) {
            throw new InvalidArgumentException('longitude must be a valid number between -180 and 180.');
        }

        $this->longitude = $long;
    }

    public function equals(GeometryInterface $geometry): bool
    {
        return $geometry instanceof self
            && $geometry->latitude === $this->latitude
            && $geometry->longitude === $this->longitude;
    }

    public function getGeoHash(int $resolution = 12): Geohash
    {
        return Geohash::fromPoint($this, $resolution);
    }

    public function round(int $precision): Point
    {
        $point = clone $this;
        $point->latitude = round($this->latitude, $precision);
        $point->longitude = round($this->longitude, $precision);

        return $point;
    }
}
