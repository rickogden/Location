<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use function count;

use InvalidArgumentException;

use function is_float;
use function is_int;
use function is_string;

use const M_PI;

use Override;
use Ricklab\Location\Converter\Unit;
use Ricklab\Location\Ellipsoid\DefaultEllipsoid;
use Ricklab\Location\Exception\BoundBoxRangeException;

final class BoundingBox implements GeometryInterface
{
    private ?Polygon $polygon = null;

    private Point $northEast;
    private ?Point $southEast = null;
    private ?Point $northWest = null;
    private Point $southWest;

    /**
     * @param float|numeric-string $radius
     *
     * @throws BoundBoxRangeException currently cannot create a bounding box over the meridian
     */
    public static function fromCenter(Point $point, float|string $radius, Unit $unit = Unit::METERS): self
    {
        $maxLat = $point->getRelativePoint($radius, 0, $unit)->getLatitude();
        $minLat = $point->getRelativePoint($radius, 180, $unit)->getLatitude();

        $radDist = (float) $radius / (float) $unit->fromMeters(DefaultEllipsoid::get()->radius());
        $radLon = $point->longitudeToRad();
        $deltaLon = asin(sin($radDist) / cos($point->latitudeToRad()));

        if (is_nan($deltaLon)) {
            throw new BoundBoxRangeException('Cannot create a bounding-box at these coordinates.');
        }
        $minLon = $radLon - $deltaLon;

        if ($minLon < deg2rad(-180)) {
            $minLon += 2.0 * M_PI;
        }
        $maxLon = $radLon + $deltaLon;

        if ($maxLon > deg2rad(180)) {
            $maxLon -= 2.0 * M_PI;
        }

        $minLon = rad2deg($minLon);
        $maxLon = rad2deg($maxLon);

        return new self($minLon, $minLat, $maxLon, $maxLat);
    }

    public static function fromGeometry(GeometryInterface $geometry): self
    {
        $maxLat = Point::MIN_LATITUDE;
        $minLat = Point::MAX_LATITUDE;
        $maxLon = Point::MIN_LONGITUDE;
        $minLon = Point::MAX_LONGITUDE;

        $points = $geometry->getPoints();

        foreach ($points as $point) {
            $lat = $point->getLatitude();
            $lon = $point->getLongitude();
            $maxLat = max($lat, $maxLat);
            $minLat = min($lat, $minLat);
            $maxLon = max($lon, $maxLon);
            $minLon = min($lon, $minLon);
        }

        return new self($minLon, $minLat, $maxLon, $maxLat);
    }

    /**
     * @param GeometryInterface[] $geometries
     */
    public static function fromGeometries(array $geometries): self
    {
        return self::fromGeometry(new GeometryCollection($geometries));
    }

    #[Override]
    public static function fromArray(array $geometries): self
    {
        if (4 !== count($geometries)) {
            throw new InvalidArgumentException('Array needs to have exactly 4 elements.');
        }

        return new self(
            self::toCoordinate($geometries[0]),
            self::toCoordinate($geometries[1]),
            self::toCoordinate($geometries[2]),
            self::toCoordinate($geometries[3]),
        );
    }

    /**
     * @return float|numeric-string
     */
    private static function toCoordinate(mixed $coordinate): float|string
    {
        if (is_float($coordinate) || is_int($coordinate)) {
            return (float) $coordinate;
        }

        if (is_string($coordinate) && is_numeric($coordinate)) {
            return $coordinate;
        }

        throw new InvalidArgumentException('Coordinate needs to be a float or a numeric-string.');
    }

    #[Override]
    public function getBBox(): self
    {
        return $this;
    }

    /**
     * @param float|numeric-string $minLon
     * @param float|numeric-string $maxLon
     * @param float|numeric-string $minLat
     * @param float|numeric-string $maxLat
     */
    public function __construct(
        float|string $minLon,
        float|string $minLat,
        float|string $maxLon,
        float|string $maxLat,
    ) {
        $this->southWest = new Point($minLon, $minLat);
        $this->northEast = new Point($maxLon, $maxLat);
    }

    /**
     * @return array{0: float, 1: float, 2: float, 3: float} Array of coordinates in the order of: minimum longitude, minimum latitude, max longitude and maximum latitude
     */
    public function getBounds(): array
    {
        return [
            $this->getMinLon(),
            $this->getMinLat(),
            $this->getMaxLon(),
            $this->getMaxLat(),
        ];
    }

    public function getCenter(): Point
    {
        $lat = ($this->getMinLat() + $this->getMaxLat()) / 2.0;
        $lon = ($this->getMinLon() + $this->getMaxLon()) / 2.0;

        return new Point($lon, $lat);
    }

    public function getMinLon(): float
    {
        return $this->southWest->getLongitude();
    }

    public function getMaxLon(): float
    {
        return $this->northEast->getLongitude();
    }

    public function getMinLat(): float
    {
        return $this->southWest->getLatitude();
    }

    public function getMaxLat(): float
    {
        return $this->northEast->getLatitude();
    }

    public function contains(GeometryInterface $geometry): bool
    {
        foreach ($geometry->getPoints() as $point) {
            $lat = $point->getLatitude();
            $lon = $point->getLongitude();

            if (
                $lat < $this->getMinLat()
                || $lat > $this->getMaxLat()
                || $lon < $this->getMinLon()
                || $lon > $this->getMaxLon()
            ) {
                return false;
            }
        }

        return true;
    }

    public function intersects(GeometryInterface $geometry): bool
    {
        [$minLon, $minLat, $maxLon, $maxLat] = $this->getBounds();
        foreach ($geometry->getPoints() as $point) {
            $lat = $point->getLatitude();
            $lon = $point->getLongitude();

            if (
                $lat >= $minLat
                && $lat <= $maxLat
                && $lon >= $minLon
                && $lon <= $maxLon
            ) {
                return true;
            }
        }

        return false;
    }

    public function getPolygon(): Polygon
    {
        if (null === $this->polygon) {
            $this->polygon = new Polygon([new LineString([
                $this->getNorthWest(),
                $this->getNorthEast(),
                $this->getSouthEast(),
                $this->getSouthWest(),
            ])]);
        }

        return $this->polygon;
    }

    public function getNorthEast(): Point
    {
        return $this->northEast;
    }

    public function getSouthEast(): Point
    {
        if (null === $this->southEast) {
            $this->southEast = new Point($this->getMaxLon(), $this->getMinLat());
        }

        return $this->southEast;
    }

    public function getNorthWest(): Point
    {
        if (null === $this->northWest) {
            $this->northWest = new Point($this->getMinLon(), $this->getMaxLat());
        }

        return $this->northWest;
    }

    public function getSouthWest(): Point
    {
        return $this->southWest;
    }

    #[Override]
    public function getPoints(): array
    {
        return $this->getPolygon()->getPoints();
    }

    #[Override]
    public function toArray(): array
    {
        return $this->getPolygon()->toArray();
    }

    public function __toString(): string
    {
        return (string) $this->getPolygon();
    }

    #[Override]
    public function wktFormat(): string
    {
        return $this->getPolygon()->wktFormat();
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return $this->getPolygon()->jsonSerialize();
    }

    #[Override]
    public function equals(GeometryInterface $geometry): bool
    {
        return $geometry instanceof self && $this->getBounds() === $geometry->getBounds();
    }

    /** @return list<LineString> */
    #[Override]
    public function getChildren(): array
    {
        return $this->getPolygon()->getChildren();
    }
}
