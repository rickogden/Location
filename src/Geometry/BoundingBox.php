<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use const M_PI;

use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Ellipsoid\DefaultEllipsoid;
use Ricklab\Location\Exception\BoundBoxRangeException;

final class BoundingBox
{
    /** @readonly  */
    private float $minLon;

    /** @readonly  */
    private float $maxLon;

    /** @readonly  */
    private float $minLat;

    /** @readonly  */
    private float $maxLat;

    private ?Polygon $polygon = null;

    /**
     * @throws BoundBoxRangeException currently cannot create a bounding box over the meridian
     */
    public static function fromCenter(Point $point, float $radius, string $unit = UnitConverter::UNIT_METERS): self
    {
        $maxLat = $point->getRelativePoint($radius, 0, $unit)->getLatitude();
        $minLat = $point->getRelativePoint($radius, 180, $unit)->getLatitude();

        $radDist = $radius / DefaultEllipsoid::get()::radius($unit);
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
            $maxLat = ($lat > $maxLat) ? $lat : $maxLat;
            $minLat = ($lat < $minLat) ? $lat : $minLat;
            $maxLon = ($lon > $maxLon) ? $lon : $maxLon;
            $minLon = ($lon < $minLon) ? $lon : $minLon;
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

    /**
     * @param array{0: float, 1: float, 2: float, 3: float} Array of coordinates in the order of: minimum longitude, minimum latitude, max longitude and maximum latitude
     */
    public static function fromArray(array $geometries): self
    {
        return new self(
            $geometries[0],
            $geometries[1],
            $geometries[2],
            $geometries[3]
        );
    }

    public function getBBox(): self
    {
        return $this;
    }

    public function __construct(float $minLon, float $minLat, float $maxLon, float $maxLat)
    {
        $this->minLon = $minLon;
        $this->maxLon = $maxLon;
        $this->minLat = $minLat;
        $this->maxLat = $maxLat;
    }

    /**
     * @return array{0: float, 1: float, 2: float, 3: float} Array of coordinates in the order of: minimum longitude, minimum latitude, max longitude and maximum latitude
     */
    public function getBounds(): array
    {
        return [
            $this->minLon,
            $this->minLat,
            $this->maxLon,
            $this->maxLat,
        ];
    }

    public function getCenter(): Point
    {
        $lat = ($this->minLat + $this->maxLat) / 2;
        $lon = ($this->minLon + $this->maxLon) / 2;

        return new Point($lon, $lat);
    }

    public function getMinLon(): float
    {
        return $this->minLon;
    }

    public function getMaxLon(): float
    {
        return $this->maxLon;
    }

    public function getMinLat(): float
    {
        return $this->minLat;
    }

    public function getMaxLat(): float
    {
        return $this->maxLat;
    }

    public function contains(GeometryInterface $geometry): bool
    {
        foreach ($geometry->getPoints() as $point) {
            $lat = $point->getLatitude();
            $lon = $point->getLongitude();

            if (
                $lat < $this->minLat
                || $lat > $this->maxLat
                || $lon < $this->minLon
                || $lon > $this->maxLon
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
                $lat >= $this->minLat
                && $lat <= $this->maxLat
                && $lon >= $this->minLon
                && $lon <= $this->maxLon
            ) {
                return true;
            }
        }

        return false;
    }

    public function getPolygon(): Polygon
    {
        if (null === $this->polygon) {
            $nw = Point::fromArray([$this->minLon, $this->maxLat]);
            $ne = Point::fromArray([$this->maxLon, $this->maxLat]);
            $se = Point::fromArray([$this->maxLon, $this->minLat]);
            $sw = Point::fromArray([$this->minLon, $this->minLat]);

            $this->polygon = new Polygon([new LineString([$nw, $ne, $se, $sw])]);
        }

        return $this->polygon;
    }

    public function getPoints(): array
    {
        return $this->getPolygon()->getPoints();
    }
}
