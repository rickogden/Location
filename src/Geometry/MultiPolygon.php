<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:22.
 */

namespace Ricklab\Location\Geometry;

class MultiPolygon implements GeometryInterface, GeometryCollectionInterface
{
    /**
     * @var Polygon[]
     */
    protected $geometries = [];

    public static function fromArray(array $geometries): self
    {
        $result = [];
        foreach ($geometries as $polygon) {
            if ($polygon instanceof Polygon) {
                $result[] = $polygon;
            } else {
                $result[] = Point::fromArray($polygon);
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
     * @return string the Well-Known Text representation of the geometry
     */
    public function toWkt(): string
    {
        return 'MULTIPOLYGON'.$this;
    }

    /**
     * @return Point[] gets all the points in a geometry. Note, order is not necessarily representative.
     */
    public function getPoints(): array
    {
        $points = [];
        foreach ($this->geometries as $polygon) {
            $points = $polygon->getPoints();
        }

        return $points;
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

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'MultiPolygon',
            'coordinates' => $this->toArray(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $ar = [];

        foreach ($this->geometries as $polygon) {
            $ar[] = $polygon->toArray();
        }

        return $ar;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '('.\implode(',', $this->geometries).')';
    }
}
