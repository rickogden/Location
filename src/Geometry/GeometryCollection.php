<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 17:18.
 */

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * Class GeometryCollection.
 */
class GeometryCollection implements GeometryInterface, GeometryCollectionInterface
{
    use GeometryTrait;

    public static function getWktType(): string
    {
        return 'GEOMETRYCOLLECTION';
    }

    public static function getGeoJsonType(): string
    {
        return 'GeometryCollection';
    }

    public static function fromArray(array $geometries): self
    {
        return new self($geometries);
    }

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
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        $json['type'] = self::getGeoJsonType();
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

        return '('.\implode(', ', $collection).')';
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
