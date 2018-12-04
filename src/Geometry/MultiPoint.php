<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:24.
 */

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Location;

class MultiPoint implements GeometryInterface, GeometryCollectionInterface, \SeekableIterator, \ArrayAccess
{
    /**
     * @var Point[]
     */
    protected $geometries;

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

    public function __construct(array $points)
    {
        $this->geometries = (function(Point ...$points) {
            return $points;
        })(...$points);
    }

    /**
     * {@inheritdoc}
     */
    public function toWkt(): string
    {
        return 'MULTIPOINT'.$this;
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

    /**
     * {@inheritdoc}
     */
    public function getPoints(): array
    {
        return $this->geometries;
    }

    /**
     * @return $this
     */
    public function addGeometry(Point $point): self
    {
        $this->geometries[] = $point;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeGeometry(Point $point): self
    {
        foreach ($this->geometries as $index => $geom) {
            if ($point === $geom) {
                unset($this->geometries[$index]);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '('.\implode(', ', $this->geometries).')';
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        $coordinates = $this->toArray();

        return ['type' => 'MultiPoint', 'coordinates' => $coordinates];
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

    public function seek($position): void
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException('Item does not exist');
        }
    }

    public function valid(): bool
    {
        return isset($this->geometries[$this->position]);
    }

    public function current(): Point
    {
        return $this->geometries[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->geometries[$offset]);
    }

    public function offsetGet($offset): Point
    {
        return $this->geometries[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof Point) {
            $value = new Point($value);
        }

        if (\is_int($offset)) {
            $this->geometries[$offset] = $value;
        } elseif (null === $offset) {
            $this->geometries[] = $value;
        } else {
            throw new \OutOfBoundsException('Key must be numeric.');
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->geometries[$offset]);
    }

    public function getBBox(): Polygon
    {
        return Location::getBBox($this);
    }
}
