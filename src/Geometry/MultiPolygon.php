<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:22.
 */

namespace Ricklab\Location\Geometry;

use IteratorAggregate;
use Ricklab\Location\Geometry\Traits\GeometryTrait;

final class MultiPolygon implements GeometryInterface, GeometryCollectionInterface, IteratorAggregate
{
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
        $this->geometries = (fn (Polygon ...$polygons): array => $polygons)(...$polygons);
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

    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }
}
