<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use IteratorAggregate;
use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * @implements IteratorAggregate<Polygon>
 * @implements GeometryCollectionInterface<Polygon>
 */
final class MultiPolygon implements GeometryInterface, GeometryCollectionInterface, IteratorAggregate
{
    /** @use GeometryTrait<Polygon> */
    use GeometryTrait;

    /**
     * @readonly
     *
     * @var list<Polygon>
     */
    protected array $geometries = [];

    public static function fromArray(array $geometries): self
    {
        $result = [];

        /** @var Polygon|array $polygon */
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
     *
     * @psalm-param list<Polygon> $polygons
     */
    public function __construct(array $polygons)
    {
        $this->geometries = $polygons;
    }

    /**
     * @return Polygon[]
     *
     * @psalm-return list<Polygon>
     */
    public function getGeometries(): array
    {
        return $this->geometries;
    }

    public function withGeometry(Polygon $polygon): self
    {
        $geometries = $this->geometries;
        $geometries[] = $polygon;

        return new self($geometries);
    }

    public function withoutGeometry(Polygon $polygon): self
    {
        $geometries = array_filter($this->geometries, fn (Polygon $p): bool => $p !== $polygon);

        return new self(array_values($geometries));
    }

    /**
     * @return Polygon[]
     *
     * @psalm-return list<Polygon>
     */
    public function getChildren(): array
    {
        return $this->geometries;
    }

    public function getBBox(): BoundingBox
    {
        return BoundingBox::fromGeometry($this);
    }
}
