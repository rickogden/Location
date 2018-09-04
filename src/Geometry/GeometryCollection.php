<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 17:18.
 */

namespace Ricklab\Location\Geometry;

/**
 * Class GeometryCollection.
 */
class GeometryCollection implements GeometryInterface, GeometryCollectionInterface
{
    /**
     * @var GeometryInterface[]
     */
    protected $geometries = [];

    /**
     * GeometryCollection constructor.
     *
     * @param GeometryInterface[] $geometries
     */
    public function __construct(array $geometries)
    {
        foreach ($geometries as $geometry) {
            if (!$geometry instanceof GeometryInterface) {
                throw new \InvalidArgumentException('Array must contain geometries only');
            }
        }
        $this->geometries = $geometries;
    }

    /**
     * @return string the Well-Known Text representation of the geometry
     */
    public function toWkt(): string
    {
        return 'GEOMETRYCOLLECTION'.$this;
    }

    /**
     * All the geometries contained as an array.
     *
     * @return GeometryInterface[]
     */
    public function toArray(): array
    {
        return $this->geometries;
    }

    /**
     * {@inheritdoc}
     */
    public function getPoints(): array
    {
        $points = [];
        foreach ($this->geometries as $geometry) {
            $geomPoints = $geometry->getPoints();
            $points += $geomPoints;
        }

        return $points;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        $json['type'] = 'GeometryCollection';
        foreach ($this->geometries as $geometry) {
            $json['geometries'][] = $geometry->jsonSerialize();
        }

        return $json;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $collection = [];
        foreach ($this->geometries as $geometry) {
            $collection[] = $geometry->toWkt();
        }

        return '('.\implode(',', $collection).')';
    }

    /**
     * All the geometries in the collection.
     *
     * @return GeometryInterface[]
     */
    public function getGeometries(): array
    {
        return $this->geometries;
    }

    /**
     * Adds a geometry to the collection.
     *
     *
     * @return $this
     */
    public function addGeometry(GeometryInterface $geometry)
    {
        $this->geometries[] = $geometry;

        return $this;
    }

    /**
     * Removes a geometry from the collection.
     *
     *
     * @return $this
     */
    public function removeGeometry(GeometryInterface $geometry)
    {
        foreach ($this->geometries as $index => $geom) {
            if ($geom === $geometry) {
                unset($this->geometries[$index]);
            }
        }

        return $this;
    }
}
