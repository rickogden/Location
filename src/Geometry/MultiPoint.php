<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:24.
 */

namespace Ricklab\Location\Geometry;

use IteratorAggregate;
use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * @implements IteratorAggregate<Point>
 */
class MultiPoint implements GeometryInterface, GeometryCollectionInterface, IteratorAggregate
{
    /** @use GeometryTrait<Point> */
    use GeometryTrait;

    /**
     * @var Point[]
     */
    protected array $geometries = [];

    public static function getWktType(): string
    {
        return 'MULTIPOINT';
    }

    public static function getGeoJsonType(): string
    {
        return 'MultiPoint';
    }

    public static function fromArray(array $geometries): self
    {
        $result = [];
        foreach ($geometries as $point) {
            if ($point instanceof Point) {
                $result[] = $point;
            } else {
                $result[] = Point::fromArray($point);
            }
        }

        return new self($result);
    }

    public function __construct(array $points)
    {
        foreach ($points as $point) {
            $this->addGeometry($point);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return Point[]
     */
    public function getGeometries(): array
    {
        return $this->getPoints();
    }

    public function addGeometry(Point $point): void
    {
        $this->geometries[] = $point;
    }

    public function removeGeometry(Point $point): void
    {
        foreach ($this->geometries as $index => $geom) {
            if ($point === $geom) {
                unset($this->geometries[$index]);
            }
        }
    }

    public function getBBox(): Polygon
    {
        return BoundingBox::fromGeometry($this);
    }

    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }
}
