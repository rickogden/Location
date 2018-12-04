<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 14/07/15
 * Time: 13:39.
 */

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Location;

/**
 * Class LineString.
 */
class LineString implements GeometryInterface, \SeekableIterator, \Countable
{
    /**
     * @var Point[]
     */
    protected $points = [];

    /**
     * @var int
     */
    protected $position = 0;

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
        if (\count($points) < 2) {
            throw new \InvalidArgumentException('array must have 2 or more elements.');
        }
        $this->points = (function (Point ...$points) {
            return $points;
        })(...$points);
    }

    /**
     * @return float The initial bearing from the first to second point
     */
    public function getInitialBearing(): float
    {
        return $this->points[0]->initialBearingTo($this->points[1]);
    }

    public function getLength($unit = 'km', $formula = null)
    {
        $distance = 0;

        for ($i = 1, $iMax = \count($this->points); $i < $iMax; ++$i) {
            $distance += $this->points[$i - 1]->distanceTo($this->points[$i], $unit, $formula);
        }

        return $distance;
    }

    /**
     * @return Point the first point
     */
    public function getFirst(): Point
    {
        return $this->points[0];
    }

    /**
     * @return Point the last point
     */
    public function getLast(): Point
    {
        return $this->points[\count($this->points) - 1];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '('.\implode(', ', $this->points).')';
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        $coordinates = $this->toArray();

        return ['type' => 'LineString', 'coordinates' => $coordinates];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $return = [];
        foreach ($this->points as $point) {
            $return[] = $point->toArray();
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function toWkt(): string
    {
        return 'LINESTRING'.$this;
    }

    public function seek($position)
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException('Item does not exist');
        }
    }

    public function valid()
    {
        return isset($this->points[$this->position]);
    }

    public function current()
    {
        return $this->points[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position ;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Gets the bounding box which will contain the entire geometry.
     */
    public function getBBox(): Polygon
    {
        return Location::getBBox($this);
    }

    /**
     * Converts LineString into a Polygon.
     */
    public function toPolygon(): Polygon
    {
        return new Polygon($this->getPoints());
    }

    /**
     * {@inheritdoc}
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    /**
     * Reverses the direction of the line.
     */
    public function reverse(): self
    {
        $this->points = \array_reverse($this->points);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->points);
    }

    public function add(Point $point): void
    {
        $this->points[] = $point;
    }
}
