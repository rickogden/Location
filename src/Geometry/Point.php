<?php

declare(strict_types=1);

/**
 * Description of Point.
 *
 * @author rick
 */

namespace Ricklab\Location\Geometry;

use InvalidArgumentException;
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

    public static function fromArray(array $point): self
    {
        if (2 !== $length = \count($point)) {
            throw new InvalidArgumentException(\sprintf('Must be an array consisting of exactly 2 elements, %d passed',
                $length));
        }

        return new self($point[0], $point[1]);
    }

    /**
     * Create a new point from Degrees, minutes and seconds.
     *
     * @param array $lat Latitude in the order of degrees, minutes, seconds[, direction]
     * @param array $lon Longitude in the order of degrees, minutes, seconds[, direction]
     *
     * @return Point
     */
    public static function fromDms(array $lat, array $lon): self
    {
        $decLat = Location::dmsToDecimal($lat[0], $lat[1], $lat[2], $lat[3] ?? 'N');

        $decLon = Location::dmsToDecimal($lon[0], $lon[1], $lon[2], $lon[3] ?? 'E');

        return new self($decLon, $decLat);
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

    /**
     * Latitude in an array of [degrees, minutes, seconds].
     */
    public function getLatitudeInDms(): array
    {
        return Location::decimalToDms($this->latitude);
    }

    /**
     * Latitude in an array of [degrees, minutes, seconds].
     */
    public function getLongitudeInDms(): array
    {
        return Location::decimalToDms($this->longitude);
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
     * @param int $formula formula to use, should either be Location::HAVERSINE or Location::VINCENTY. Defaults to
     *                     Location::$defaultFormula
     *
     * @return float the distance
     */
    public function distanceTo(Point $point2, string $unit = 'km', int $formula = Location::FORMULA_HAVERSINE): float
    {
        return Location::calculateDistance($this, $point2, $unit, $formula);
    }

    /**
     * Find a location a distance and bearing from this one.
     *
     * @param float  $distance distance to other point
     * @param float  $bearing  initial bearing to other point
     * @param string $unit     The unit the distance is in
     */
    public function getRelativePoint(float $distance, float $bearing, string $unit = 'km'): Point
    {
        $rad     = Location::getEllipsoid()->radius($unit);
        $lat1    = $this->latitudeToRad();
        $lon1    = $this->longitudeToRad();
        $bearing = \deg2rad($bearing);

        $lat2 = \sin($lat1) * \cos($distance / $rad) +
                \cos($lat1) * \sin($distance / $rad) * \cos($bearing);
        $lat2 = \asin($lat2);

        $lon2y = \sin($bearing) * \sin($distance / $rad) * \cos($lat1);
        $lon2x = \cos($distance / $rad) - \sin($lat1) * \sin($lat2);
        $lon2  = $lon1 + \atan2($lon2y, $lon2x);

        return new self(\rad2deg($lon2), \rad2deg($lat2));
    }

    /**
     * Get the latitude in Rads.
     *
     * @return float Latitude in Rads
     */
    public function latitudeToRad(): float
    {
        return \deg2rad($this->latitude);
    }

    /**
     * Get the longitude in Rads.
     *
     * @return float Longitude in Rads
     */
    public function longitudeToRad(): float
    {
        return \deg2rad($this->longitude);
    }

    /**
     * Get the initial bearing from this Point to another.
     *
     * @return float bearing
     */
    public function initialBearingTo(Point $point2): float
    {
        return Location::getInitialBearing($this, $point2);
    }

    /**
     * Get the final bearing from this Point to another.
     *
     * @return float bearing
     */
    public function finalBearingTo(Point $point2): float
    {
        return Location::getFinalBearing($this, $point2);
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
    public function getMidpoint(Point $point): Point
    {
        return $this->getFractionAlongLineTo($point, 0.5);
    }

    /**
     * Returns the point which is a fraction along the line between 0 and 1.
     *
     * @param float $fraction between 0 and 1
     *
     * @throw \InvalidArgumentException
     */
    public function getFractionAlongLineTo(Point $point, float $fraction): self
    {
        return Location::getFractionAlongLineBetween($this, $point, $fraction);
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
    public function getBBoxByRadius(float $radius, string $unit = Location::UNIT_KM): BoundingBox
    {
        return BoundingBox::fromCenter($this, $radius, $unit);
    }

    /**
     * Converts point to Well-Known Text.
     */
    public function toWkt(): string
    {
        return \sprintf('%s(%s)', self::getWktType(), (string)$this);
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
        if ($lat > self::MAX_LATITUDE || $lat < self::MIN_LATITUDE || \is_nan($lat)) {
            throw new InvalidArgumentException('latitude must be a valid number between -90 and 90.');
        }

        $this->latitude = $lat;
    }

    private function setLongitude(float $long): void
    {
        if ($long > self::MAX_LONGITUDE || $long < self::MIN_LONGITUDE || \is_nan($long)) {
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

    public function getGeoHash(int $resolution = 12): GeoHash
    {
        return GeoHash::fromPoint($this, $resolution);
    }

    public function round(int $precision): Point
    {
        $point            = clone $this;
        $point->latitude  = round($this->latitude, $precision);
        $point->longitude = round($this->longitude, $precision);

        return $point;
    }
}
