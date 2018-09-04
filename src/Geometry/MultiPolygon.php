<?php
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:22
 */

namespace Ricklab\Location\Geometry;

class MultiPolygon implements GeometryInterface, GeometryCollectionInterface
{
    /**
     * @var Polygon[]
     */
    protected $geometries = [];

    /**
     * @param Polygon[] $polygons
     */
    public function __construct(array $polygons)
    {
        foreach ($polygons as $polygon) {
            if (is_array($polygon)) {
                $polygon = new Polygon($polygon);
            }
            if (! $polygon instanceof Polygon) {
                throw new \InvalidArgumentException('$polygons must be an array of Polygon objects');
            } else {
                $this->geometries[] = $polygon;
            }
        }
    }

    /**
     * @return string the Well-Known Text representation of the geometry
     */
    public function toWkt()
    {
        return 'MULTIPOLYGON' . (string) $this;
    }

    /**
     * @return Point[] gets all the points in a geometry. Note, order is not necessarily representative.
     */
    public function getPoints()
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
    public function getGeometries()
    {
        return $this->geometries;
    }

    /**
     * @param Polygon $polygon
     *
     * @return $this
     */
    public function addGeometry(Polygon $polygon)
    {
        $this->geometries[] = $polygon;

        return $this;
    }

    /**
     * @param Polygon $polygon
     *
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
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $json = [
            'type'        => 'MultiPolygon',
            'coordinates' => $this->toArray()
        ];

        return $json;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $ar = [];

        foreach ($this->geometries as $polygon) {
            $ar[] = $polygon->toArray();
        }

        return $ar;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return '(' . implode(',', $this->geometries) . ')';
    }
}
