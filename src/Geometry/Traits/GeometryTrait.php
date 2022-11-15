<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry\Traits;

use ArrayIterator;

use function count;

use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\Point;

/**
 * @author Rick Ogden <rick@rickogden.com>
 */
trait GeometryTrait
{
    use TransformationTrait;

    /**
     * @return GeometryInterface[]
     * @psalm-return list<GeometryInterface>
     */
    abstract protected function getGeometryArray(): array;

    public function __toString(): string
    {
        return sprintf('(%s)', implode(', ', $this->getGeometryArray()));
    }

    /**
     * @return array[]
     * @psalm-return list<array>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (GeometryInterface $geometry): array => $geometry->toArray(),
            $this->getGeometryArray()
        );
    }

    /**
     * @return Point[]
     * @psalm-return list<Point>
     */
    public function getPoints(): array
    {
        $points = array_map(
            static fn (GeometryInterface $geometry): array => $geometry->getPoints(),
            $this->getGeometryArray()
        );

        return array_merge(...$points);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getGeometryArray());
    }

    public function equals(GeometryInterface $geometry): bool
    {
        if (!$geometry instanceof self) {
            return false;
        }

        if (count($this->geometries) !== count($geometry->geometries)) {
            return false;
        }

        foreach ($this->geometries as $i => $geom) {
            if (!$geometry->geometries[$i]->equals($geom)) {
                return false;
            }
        }

        return true;
    }
}
