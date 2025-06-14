<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use InvalidArgumentException;

use Ricklab\Location\Converter\Unit;
use const M_PI;

use Ricklab\Location\Converter\NativeUnitConverter;
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
     * @throws BoundBoxRangeException currently cannot create a bounding box over the meridian
     */
    public static function fromCenter(Point $point, float $radius, Unit $unit = Unit::METERS): self
    {
        $maxLat = $point->getRelativePoint($radius, 0, $unit)->getLatitude();
        $minLat = $point->getRelativePoint($radius, 180, $unit)->getLatitude();

        $radDist = $radius / DefaultEllipsoid::get()->radius($unit);
        $radLon = $point->longitudeToRad();
        $deltaLon = asin(sin($radDist) / cos($point->latitudeToRad()));

        if (is_nan($deltaLon)) {
            throw new BoundBoxRangeException('Cannot create a bounding-box at these coordinates.');
        }
        $minLon = $radLon - $deltaLon;

        if ($minLon < deg2rad(-180)) {
            $minLon += 2 * M_PI;
        }
        $maxLon = $radLon + $deltaLon;

        if ($maxLon > deg2rad(180)) {
            $maxLon -= 2 * M_PI;
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

    public static function fromArray(array $geometries): self
    {
        if (!is_numeric($geometries[0]) || !is_numeric($geometries[1]) || !is_numeric($geometries[2]) || !is_numeric($geometries[3])) {
            throw new InvalidArgumentException('Array element needs to be a float.');
        }

        return new self(
            (float) $geometries[0],
            (float) $geometries[1],
            (float) $geometries[2],
            (float) $geometries[3],
        );
    }

    public function getBBox(): self
    {
        return $this;
    }

    public function __construct(float $minLon, float $minLat, float $maxLon, float $maxLat)
    {
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
        $lat = ($this->getMinLat() + $this->getMaxLat()) / 2;
        $lon = ($this->getMinLon() + $this->getMaxLon()) / 2;

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
        foreach ($geometry->getPoints() as $point) {
            $lat = $point->getLatitude();
            $lon = $point->getLongitude();

            if (
                $lat >= $this->getMinLat()
                && $lat <= $this->getMaxLat()
                && $lon >= $this->getMinLon()
                && $lon <= $this->getMaxLon()
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

    public function getPoints(): array
    {
        return $this->getPolygon()->getPoints();
    }

    public function toArray(): array
    {
        return $this->getPolygon()->toArray();
    }

    public function __toString(): string
    {
        return (string) $this->getPolygon();
    }

    public function wktFormat(): string
    {
        return $this->getPolygon()->wktFormat();
    }

    public function jsonSerialize(): array
    {
        return $this->getPolygon()->jsonSerialize();
    }

    public function equals(GeometryInterface $geometry): bool
    {
        return $geometry instanceof self && $this->getBounds() === $geometry->getBounds();
    }

    /** @return list<LineString> */
    public function getChildren(): array
    {
        return $this->getPolygon()->getChildren();
    }
}
