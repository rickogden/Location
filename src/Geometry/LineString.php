<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 14/07/15
 * Time: 13:39.
 */

namespace Ricklab\Location\Geometry;

use function count;

use InvalidArgumentException;
use IteratorAggregate;
use Ricklab\Location\Calculator\DistanceCalculator;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * @implements IteratorAggregate<Point>
 */
class LineString implements GeometryInterface, IteratorAggregate
{
    /** @use GeometryTrait<Point> */
    use GeometryTrait;

    /**
     * @var Point[]
     */
    protected array $geometries = [];
    protected int $position = 0;

    public static function getWktType(): string
    {
        return 'LINESTRING';
    }

    public static function getGeoJsonType(): string
    {
        return 'LineString';
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

    /**
     * @param Point[] $points the points, or the starting point
     */
    public function __construct(array $points)
    {
        if (count($points) < 2) {
            throw new InvalidArgumentException('array must have 2 or more elements.');
        }
        foreach ($points as $point) {
            $this->add($point);
        }
    }

    /**
     * @return float The initial bearing from the first to second point
     */
    public function getInitialBearing(): float
    {
        return $this->geometries[0]->initialBearingTo($this->geometries[1]);
    }

    /**
     * @param string                  $unit       defaults to "meters"
     * @param DistanceCalculator|null $calculator The calculator that is used for calculating the distance. If null, uses DefaultDistanceCalculator
     */
    public function getLength(string $unit = UnitConverter::UNIT_METERS, ?DistanceCalculator $calculator = null): float
    {
        $distance = 0;

        for ($i = 1, $iMax = count($this->geometries); $i < $iMax; ++$i) {
            $distance += $this->geometries[$i - 1]->distanceTo($this->geometries[$i], $unit, $calculator);
        }

        return $distance;
    }

    /**
     * @return Point the first point
     */
    public function getFirst(): Point
    {
        return $this->geometries[0];
    }

    /**
     * @return Point the last point
     */
    public function getLast(): Point
    {
        return end($this->geometries);
    }

    /**
     * Gets the bounding box which will contain the entire geometry.
     */
    public function getBBox(): Polygon
    {
        return BoundingBox::fromGeometry($this);
    }

    /**
     * Converts LineString into a Polygon.
     */
    public function toPolygon(): Polygon
    {
        return new Polygon([$this]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPoints(): array
    {
        return $this->geometries;
    }

    /**
     * A new LineString in the reverse direction.
     */
    public function reverse(): self
    {
        return new self(array_reverse($this->geometries));
    }

    private function add(Point $point): void
    {
        $this->geometries[] = $point;
    }

    /**
     * A new LineString with the new point added.
     */
    public function addPoint(Point $point): self
    {
        $points = $this->geometries;
        $points[] = $point;

        return new self($points);
    }

    public function isClosedShape(): bool
    {
        return $this->getFirst()->equals($this->getLast());
    }

    public function contains(Point $point): bool
    {
        foreach ($this->geometries as $geo) {
            if ($geo->equals($point)) {
                return true;
            }
        }

        return false;
    }

    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }
}
