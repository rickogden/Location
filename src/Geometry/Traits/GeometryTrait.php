<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry\Traits;

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
     */
    abstract protected function getGeometryArray(): array;

    public function __toString(): string
    {
        return \sprintf('(%s)', \implode(', ', $this->getGeometryArray()));
    }

    public function toArray(): array
    {
        $return = [];
        foreach ($this->getGeometryArray() as $geometry) {
            $return[] = $geometry->toArray();
        }

        return $return;
    }

    /**
     * @return Point[]
     */
    public function getPoints(): array
    {
        $points = [];
        foreach ($this->getGeometryArray() as $geometry) {
            $linePoints = $geometry->getPoints();
            $points[] = $linePoints;
        }

        return \array_merge(...$points);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->getGeometryArray());
    }
}
