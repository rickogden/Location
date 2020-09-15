<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Exception\BoundBoxRangeException;
use Ricklab\Location\Location;

class BoundingBox extends Polygon
{
    /** @var float */
    private $minLon;

    /** @var float */
    private $maxLon;

    /** @var float */
    private $minLat;

    /** @var float */
    private $maxLat;

    public static function fromCenter(Point $point, float $radius, string $unit = Location::UNIT_KM): self
    {
        $north = $point->getRelativePoint($radius, 0, $unit);
        $south = $point->getRelativePoint($radius, 180, $unit);

        $limits = [
            'n' => $north->getLatitude(),
            's' => $south->getLatitude(),
        ];

        $radDist = $radius / Location::getEllipsoid()->radius($unit);
        $radLon = $point->longitudeToRad();
        $deltaLon = \asin(\sin($radDist) / \cos($point->latitudeToRad()));

        if (\is_nan($deltaLon)) {
            throw new BoundBoxRangeException('Cannot create a bounding-box at these coordinates.');
        }
        $minLon = $radLon - $deltaLon;

        if ($minLon < \deg2rad(-180)) {
            $minLon += 2 * \M_PI;
        }
        $maxLon = $radLon + $deltaLon;

        if ($maxLon > \deg2rad(180)) {
            $maxLon -= 2 * \M_PI;
        }

        $limits['w'] = \rad2deg($minLon);
        $limits['e'] = \rad2deg($maxLon);

        return new self($limits['w'], $limits['s'], $limits['e'], $limits['n']);
    }

    public static function fromGeometry(GeometryInterface $geometry): self
    {
        $maxLat = Point::MIN_LATITUDE;
        $minLat = Point::MAX_LATITUDE;
        $maxLon = Point::MIN_LONGITUDE;
        $minLon = Point::MAX_LONGITUDE;

        $points = $geometry->getPoints();

        /** @var Point $point */
        foreach ($points as $point) {
            $maxLat = ($point->getLatitude() > $maxLat) ? $point->getLatitude() : $maxLat;
            $minLat = ($point->getLatitude() < $minLat) ? $point->getLatitude() : $minLat;
            $maxLon = ($point->getLongitude() > $maxLon) ? $point->getLongitude() : $maxLon;
            $minLon = ($point->getLongitude() < $minLon) ? $point->getLongitude() : $minLon;
        }

        return new self($minLon, $minLat, $maxLon, $maxLat);
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
        $nw = Point::fromArray([$minLon, $maxLat]);
        $ne = Point::fromArray([$maxLon, $maxLat]);
        $se = Point::fromArray([$maxLon, $minLat]);
        $sw = Point::fromArray([$minLon, $minLat]);

        parent::__construct([new LineString([$nw, $ne, $se, $sw])]);
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
}
