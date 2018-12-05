<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:22.
 */

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Geometry\Traits\GeometryCollectionTrait;

class MultiPolygon implements GeometryInterface, GeometryCollectionInterface, \IteratorAggregate
{
    use GeometryCollectionTrait;

    public static function getWktType(): string
    {
        return 'MULTIPOLYGON';
    }

    public static function getGeoJsonType(): string
    {
        return 'MultiPolygon';
    }

    public static function fromArray(array $geometries): self
    {
        $result = [];
        foreach ($geometries as $polygon) {
            if ($polygon instanceof Polygon) {
                $result[] = $polygon;
            } else {
                $result[] = Polygon::fromArray($polygon);
            }
        }

        return new self($result);
    }

    /**
     * @param Polygon[] $polygons
     */
    public function __construct(array $polygons)
    {
        foreach ($polygons as $polygon) {
            if (\is_array($polygon)) {
                $polygon = new Polygon($polygon);
            }

            if (!$polygon instanceof Polygon) {
                throw new \InvalidArgumentException('$polygons must be an array of Polygon objects');
            }
            $this->geometries[] = $polygon;
        }
    }

    /**
     * @return Polygon[]
     */
    public function getGeometries(): array
    {
        return $this->geometries;
    }

    /**
     * @return $this
     */
    public function addGeometry(Polygon $polygon)
    {
        $this->geometries[] = $polygon;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeGeometry(Polygon $polygon)
    {
        foreach ($this->geometries as $index => $geom) {
            if ($polygon === $geom) {
                unset($this->geometries[$index]);
            }
        }

        return $this;
    }
}
