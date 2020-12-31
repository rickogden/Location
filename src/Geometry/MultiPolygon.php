<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:22.
 */

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Geometry\Traits\GeometryTrait;

class MultiPolygon implements GeometryInterface, GeometryCollectionInterface, \IteratorAggregate
{
    use GeometryTrait;

    /**
     * @var Polygon[]
     */
    protected array $geometries = [];

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
            $this->addGeometry($polygon);
        }
    }

    /**
     * @return Polygon[]
     */
    public function getGeometries(): array
    {
        return $this->geometries;
    }

    public function addGeometry(Polygon $polygon): void
    {
        $this->geometries[] = $polygon;
    }

    public function removeGeometry(Polygon $polygon): void
    {
        foreach ($this->geometries as $index => $geom) {
            if ($polygon === $geom) {
                unset($this->geometries[$index]);
            }
        }
    }

    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }
}
