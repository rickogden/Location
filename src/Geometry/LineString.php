<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use function count;

use InvalidArgumentException;

use function is_array;

use IteratorAggregate;
use Ricklab\Location\Calculator\DistanceCalculator;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * @implements IteratorAggregate<Point>
 */
final class LineString implements GeometryInterface, IteratorAggregate
{
    /** @use GeometryTrait<Point> */
    use GeometryTrait;

    /**
     * @readonly
     *
     * @var Point[]
     *
     * @psalm-var non-empty-list<Point>
     */
    protected readonly array $geometries;
    protected int $position = 0;

    public static function fromArray(array $geometries): self
    {
        $result = [];
        /** @var Point|array|mixed $point */
        foreach ($geometries as $point) {
            if ($point instanceof Point) {
                $result[] = $point;
            } elseif (is_array($point)) {
                $result[] = Point::fromArray($point);
            } else {
                throw new InvalidArgumentException('Array element needs to be either an instance of Point or array.');
            }
        }

        return new self($result);
    }

    /**
     * @param Point[] $points the points, or the starting point
     *
     * @psalm-param list<Point> $points the points, or the starting point
     */
    public function __construct(array $points)
    {
        if (count($points) < 2) {
            throw new InvalidArgumentException('array must have 2 or more elements.');
        }
        $this->geometries = $points;
    }

    /**
     * @return float The initial bearing from the first to second point
     */
    public function getInitialBearing(): float
    {
        return $this->geometries[0]->initialBearingTo($this->geometries[1]);
    }

    /**
     * @param string $unit defaults to "meters"
     *
     * @psalm-param UnitConverter::UNIT_*                  $unit       defaults to "meters"
     *
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
        return $this->geometries[array_key_last($this->geometries)];
    }

    /**
     * Gets the bounding box which will contain the entire geometry.
     */
    public function getBBox(): BoundingBox
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

    /**
     * @deprecated use LineString::withPoint() instead
     */
    public function addPoint(Point $point): self
    {
        return $this->withPoint($point);
    }

    /**
     * A new LineString with the new point added.
     */
    public function withPoint(Point $point): self
    {
        $points = $this->geometries;
        $points[] = $point;

        return new self($points);
    }

    public function getClosedShape(): self
    {
        if ($this->isClosedShape()) {
            return $this;
        }

        return $this->withPoint($this->getFirst());
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

    /**
     * @return list<Point>
     */
    public function getChildren(): array
    {
        return $this->geometries;
    }
}
