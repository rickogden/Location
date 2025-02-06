<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use IteratorAggregate;
use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * @implements IteratorAggregate<Point>
 * @implements GeometryCollectionInterface<Point>
 */
final class MultiPoint implements GeometryInterface, GeometryCollectionInterface, IteratorAggregate
{
    /** @use GeometryTrait<Point> */
    use GeometryTrait;

    /**
     * @readonly
     *
     * @var list<Point>
     */
    protected array $geometries = [];

    public static function fromArray(array $geometries): self
    {
        $result = [];
        /** @var Point|array $point */
        foreach ($geometries as $point) {
            if ($point instanceof Point) {
                $result[] = $point;
            } else {
                $result[] = Point::fromArray($point);
            }
        }

        return new self($result);
    }

    /**
     * @param Point[] $points
     *
     * @psalm-param list<Point> $points
     */
    public function __construct(array $points)
    {
        $this->geometries = $points;
    }

    /**
     * @return Point[]
     *
     * @psalm-return list<Point>
     */
    public function getGeometries(): array
    {
        return $this->getPoints();
    }

    public function withGeometry(Point $point): self
    {
        $geometries = $this->geometries;
        $geometries[] = $point;

        return new self($geometries);
    }

    public function withoutGeometry(Point $point): self
    {
        $geometries = array_filter($this->geometries, fn (Point $p): bool => $p !== $point);

        return new self(array_values($geometries));
    }

    public function getBBox(): BoundingBox
    {
        return BoundingBox::fromGeometry($this);
    }

    /**
     * @return Point[]
     *
     * @psalm-return list<Point>
     */
    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }
}
