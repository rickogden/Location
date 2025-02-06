<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Geometry\Traits\GeometryTrait;
use Ricklab\Location\Transformer\GeoJsonTransformer;
use Ricklab\Location\Transformer\WktTransformer;

use function sprintf;

/**
 * @implements GeometryCollectionInterface<GeometryInterface>
 */
final class GeometryCollection implements GeometryInterface, GeometryCollectionInterface
{
    /** @use GeometryTrait<GeometryInterface> */
    use GeometryTrait;

    /**
     * @var list<GeometryInterface>
     */
    protected array $geometries = [];

    public static function fromArray(array $geometries): self
    {
        /** @psalm-suppress MixedArgument $geometries */
        $geometries = (fn (GeometryInterface ...$geometries): array => $geometries)(...$geometries);

        return new self($geometries);
    }

    /**
     * GeometryCollection constructor.
     *
     * @param GeometryInterface[] $geometries
     */
    public function __construct(array $geometries)
    {
        $this->geometries = array_values((fn (GeometryInterface ...$geometries): array => $geometries)(...$geometries));
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

    /**
     * @return list<GeometryInterface>
     */
    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }

    public function getBBox(): BoundingBox
    {
        return BoundingBox::fromGeometry($this);
    }
}
