<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry\Traits;

use ArrayIterator;

use function count;

use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\Point;

use function sprintf;

/**
 * @template T of GeometryInterface
 */
trait GeometryTrait
{
    use TransformationTrait;

    /**
     * @return GeometryInterface&T[]
     *
     * @psalm-return list<GeometryInterface&T>
     */
    abstract public function getChildren(): array;

    public function __toString(): string
    {
        return $this->wktFormat();
    }

    public function wktFormat(): string
    {
        return sprintf(
            '(%s)',
            implode(
                ', ',
                array_map(
                    fn (GeometryInterface $g): string => $g->wktFormat(),
                    $this->getChildren()
                )
            )
        );
    }

    /**
     * @return array[]
     *
     * @psalm-return list<array>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (GeometryInterface $geometry): array => $geometry->toArray(),
            $this->getChildren()
        );
    }

    /**
     * @return Point[]
     *
     * @psalm-return list<Point>
     */
    public function getPoints(): array
    {
        $points = array_map(
            static fn (GeometryInterface $geometry): array => $geometry->getPoints(),
            $this->getChildren()
        );

        return array_merge(...$points);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getChildren());
    }

    public function equals(GeometryInterface $geometry): bool
    {
        if ($geometry === $this) {
            return true;
        }

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
