<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 14/07/15
 * Time: 13:39.
 */

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Geometry\Traits\GeometryTrait;
use Ricklab\Location\Location;

/**
 * Class LineString.
 */
class LineString implements GeometryInterface, \IteratorAggregate
{
    use GeometryTrait;

    /**
     * @var Point[]
     */
    protected $geometries = [];

    /**
     * @var int
     */
    protected $position = 0;

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
        if (\count($points) < 2) {
            throw new \InvalidArgumentException('array must have 2 or more elements.');
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

    public function getLength($unit = 'km', $formula = null)
    {
        $distance = 0;

        for ($i = 1, $iMax = \count($this->geometries); $i < $iMax; ++$i) {
            $distance += $this->geometries[$i - 1]->distanceTo($this->geometries[$i], $unit, $formula);
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
        return $this->geometries[\count($this->geometries) - 1];
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
        foreach ($this->geometries as $point) {
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
     * Reverses the direction of the line.
     */
    public function reverse(): self
    {
        return new self(\array_reverse($this->geometries));
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
        return (string) $this->geometries[0] === (string) \end($this->geometries);
    }
}
