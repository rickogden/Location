<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 17:18.
 */

namespace Ricklab\Location\Geometry;

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
     * @var list<GeometryInterface>
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
        $this->geometries = (fn (GeometryInterface ...$geometries): array => $geometries)(...$geometries);
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
     *
     * @psalm-return list<GeometryInterface>
     */
    public function getGeometries(): array
    {
        return $this->geometries;
    }

    /**
     * Adds a geometry to the collection.
     */
    public function withGeometry(GeometryInterface $geometry): self
    {
        $geometries = $this->geometries;
        $geometries[] = $geometry;

        return new self($geometries);
    }

    /**
     * Removes a geometry from the collection.
     */
    public function removeGeometry(GeometryInterface $geometry): self
    {
        $geometries = array_filter($this->geometries, fn (GeometryInterface $g): bool => $g !== $geometry);

        return new self(array_values($geometries));
    }

    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }
}
