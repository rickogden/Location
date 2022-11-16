<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 17:18.
 */

namespace Ricklab\Location\Geometry;

use InvalidArgumentException;
use Ricklab\Location\Geometry\Traits\GeometryTrait;
use Ricklab\Location\Transformer\GeoJsonTransformer;
use Ricklab\Location\Transformer\WktTransformer;

/**
 * Class GeometryCollection.
 */
final class GeometryCollection implements GeometryInterface, GeometryCollectionInterface
{
    use GeometryTrait;

    /**
     * @var GeometryInterface[]
     */
    protected array $geometries = [];

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
                throw new InvalidArgumentException('Array must contain geometries only');
            }
        }
        $this->geometries = $geometries;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return GeoJsonTransformer::jsonArray($this);
    }

    public function __toString(): string
    {
        return $this->wktFormat();
    }

    public function wktFormat(): string
    {
        return sprintf(
            '(%s)',
            implode(
                ', ',
                array_map(
                    fn (GeometryInterface $g) => WktTransformer::encode($g),
                    $this->geometries
                )
            )
        );
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
     */
    public function addGeometry(GeometryInterface $geometry): void
    {
        $this->geometries[] = $geometry;
    }

    /**
     * Removes a geometry from the collection.
     */
    public function removeGeometry(GeometryInterface $geometry): void
    {
        foreach ($this->geometries as $index => $geom) {
            if ($geom === $geometry) {
                unset($this->geometries[$index]);
            }
        }
    }

    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }
}
