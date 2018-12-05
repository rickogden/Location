<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:24.
 */

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Geometry\Traits\GeometryCollectionTrait;
use Ricklab\Location\Location;

class MultiPoint implements GeometryInterface, GeometryCollectionInterface, \IteratorAggregate
{
    use GeometryCollectionTrait;

    public static function getWktType(): string
    {
        return 'MULTIPOINT';
    }

    public static function getGeoJsonType(): string
    {
        return 'MultiPoint';
    }


    public static function fromArray(array $geometries): self
    {
        $result = [];
        foreach ($geometries as $point) {
            if ($point instanceof Point) {
                $result[] = $point;
            } else {
                $result[] = Point::fromArray($point);
            }
        }

        return new self($result);
    }

    public function __construct(array $points)
    {
        $this->geometries = (function (Point ...$points) {
            return $points;
        })(...$points);
    }

    /**
     * {@inheritdoc}
     *
     * @return Point[]
     */
    public function getGeometries(): array
    {
        return $this->getPoints();
    }

    /**
     * @return $this
     */
    public function addGeometry(Point $point): self
    {
        $this->geometries[] = $point;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeGeometry(Point $point): self
    {
        foreach ($this->geometries as $index => $geom) {
            if ($point === $geom) {
                unset($this->geometries[$index]);
            }
        }

        return $this;
    }

    public function getBBox(): Polygon
    {
        return Location::getBBox($this);
    }
}
