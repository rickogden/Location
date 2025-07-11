<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use function array_values;
use function count;

use InvalidArgumentException;
use IteratorAggregate;
use Override;
use Ricklab\Location\Calculator\DistanceCalculator;
use Ricklab\Location\Converter\Unit;
use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * @implements IteratorAggregate<LineString>
 */
final class Polygon implements GeometryInterface, IteratorAggregate
{
    /** @use GeometryTrait<LineString> */
    use GeometryTrait;

    /**
     * @var non-empty-list<LineString>
     */
    private readonly array $geometries;

    #[Override]
    public static function fromArray(array $geometries): self
    {
        $result = [];

        /** @var LineString|array $lineString */
        foreach ($geometries as $lineString) {
            if ($lineString instanceof LineString) {
                $result[] = $lineString;
            } else {
                $result[] = LineString::fromArray($lineString);
            }
        }

        return new self($result);
    }

    /**
     * Pass in a LineString to create a Polygon or multiple LineStrings for a Polygon with holes in.
     *
     * @param $lines LineString[]
     */
    public function __construct(array $lines)
    {
        if (count($lines) < 1) {
            throw new InvalidArgumentException('array must have 1 or more elements.');
        }

        $this->geometries = array_map(
            fn (LineString $ls): LineString => $ls->getClosedShape(),
            array_values($lines)
        );
    }

    /**
     * The length of the perimeter of the outer-most polygon in unit specified.
     *
     * @param Unit                    $unit       defaults to "meters"
     * @param DistanceCalculator|null $calculator The calculator that is used for calculating the distance. If null, uses DefaultDistanceCalculator.
     */
    public function getPerimeter(Unit $unit = Unit::METERS, ?DistanceCalculator $calculator = null): float
    {
        return $this->geometries[0]->getLength($unit, $calculator);
    }

    #[Override]
    public function getBBox(): BoundingBox
    {
        return BoundingBox::fromGeometry($this);
    }

    /**
     * @return LineString[]
     *
     * @psalm-return list<LineString>
     */
    public function getLineStrings(): array
    {
        return $this->geometries;
    }

    /**
     * @return LineString[]
     *
     * @psalm-return list<LineString>
     */
    #[Override]
    public function getChildren(): array
    {
        return $this->geometries;
    }
}
