<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Converter\Axis;
use function count;

use InvalidArgumentException;

use function is_string;

use Ricklab\Location\Calculator\BearingCalculator;
use Ricklab\Location\Calculator\CalculatorRegistry;
use Ricklab\Location\Calculator\DefaultDistanceCalculator;
use Ricklab\Location\Calculator\DistanceCalculator;
use Ricklab\Location\Calculator\FractionAlongLineCalculator;
use Ricklab\Location\Converter\DegreesMinutesSeconds;
use Ricklab\Location\Converter\Unit;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Converter\UnitConverterRegistry;
use Ricklab\Location\Ellipsoid\DefaultEllipsoid;
use Ricklab\Location\Ellipsoid\EllipsoidInterface;
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

    private readonly float $longitude;
    private readonly float $latitude;
    private readonly string $longitudeString;
    private readonly string $latitudeString;

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
            if (Axis::LONGITUDE === $dms->getAxis()) {
                $longitude = $dms;

                continue;
            }

            if (Axis::LATITUDE === $dms->getAxis()) {
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
        /** @psalm-suppress DocblockTypeContradiction for safety */
        if (is_string($lat) && !is_numeric($lat)) {
            throw new InvalidArgumentException('latitude must be a valid number between -90 and 90.');
        }

        $this->latitude = (float) $lat;
        $this->latitudeString = (string) $lat;

        /** @psalm-suppress DocblockTypeContradiction for safety */
        if (is_string($long) && !is_numeric($long)) {
            throw new InvalidArgumentException('longitude must be a valid number between -180 and 180.');
        }

        $this->longitude = (float) $long;
        $this->longitudeString = (string) $long;

        if (!self::validateLatitude($this->latitude)) {
            throw new InvalidArgumentException('latitude must be a valid number between -90 and 90.');
        }

        if (!self::validLongitude($this->longitude)) {
            throw new InvalidArgumentException('longitude must be a valid number between -180 and 180.');
        }
    }

    public function getLatitudeInDms(): DegreesMinutesSeconds
    {
        return DegreesMinutesSeconds::fromDecimal($this->latitude, Axis::LATITUDE);
    }

    public function getLongitudeInDms(): DegreesMinutesSeconds
    {
        return DegreesMinutesSeconds::fromDecimal($this->longitude, Axis::LONGITUDE);
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
     * @param Unit                    $unit       Defaults to meters
     * @param DistanceCalculator|null $calculator The calculator that is used for calculating the distance. If null, uses DefaultDistanceCalculator.
     *
     * @return float|numeric-string the distance
     */
    public function distanceTo(
        Point $point2,
        Unit $unit = Unit::METERS,
        ?DistanceCalculator $calculator = null,
        ?UnitConverter $unitConverter = null,
    ): float|string {
        $calculator ??= CalculatorRegistry::getDistanceCalculator();
        $unitConverter ??= UnitConverterRegistry::getUnitConverter();

        $result = $calculator->calculateDistance($this, $point2, DefaultEllipsoid::get());

        return $unitConverter->convertFromMeters($result, $unit);
    }

    /**
     * Find a location a distance and bearing from this one.
     *
     * @param float|numeric-string $distance distance to other point
     * @param float|numeric-string $bearing  initial bearing to other point
     */
    public function getRelativePoint(
        float|string $distance,
        float|string $bearing,
        Unit $unit = Unit::METERS,
        ?BearingCalculator $bearingCalculator = null,
        ?EllipsoidInterface $ellipsoid = null,
    ): Point {
        $bearingCalculator ??= CalculatorRegistry::getBearingCalculator();
        $ellipsoid ??= DefaultEllipsoid::get();

        return $bearingCalculator->calculateRelativePoint($ellipsoid, $this, $distance, $bearing, $unit);
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
     * @return float|numeric-string bearing
     */
    public function initialBearingTo(Point $point2, ?BearingCalculator $bearingCalculator = null): float|string
    {
        $bearingCalculator ??= CalculatorRegistry::getBearingCalculator();

        return $bearingCalculator->calculateInitialBearing($this, $point2);
    }

    /**
     * Get the final bearing from this Point to another.
     *
     * @return float|numeric-string bearing
     */
    public function finalBearingTo(Point $point2, ?BearingCalculator $bearingCalculator = null): float|string
    {
        $bearingCalculator ??= CalculatorRegistry::getBearingCalculator();

        return $bearingCalculator->calculateFinalBearing($this, $point2);
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
    public function getMidpoint(Point $point, ?FractionAlongLineCalculator $calculator = null): self
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
    public function getFractionAlongLineTo(
        Point $point,
        float $fraction,
        ?FractionAlongLineCalculator $calculator = null,
    ): self {
        $calculator ??= CalculatorRegistry::getFractionAlongLineCalculator();

        return $calculator->calculateFractionAlongLine(
            $this,
            $point,
            $fraction,
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
    public function getBBoxByRadius(float $radius, Unit $unit = Unit::METERS): BoundingBox
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
        $latitude = round($this->latitude, $precision);
        $longitude = round($this->longitude, $precision);

        return new self($longitude, $latitude);
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
