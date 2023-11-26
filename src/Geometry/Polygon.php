<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use IteratorAggregate;
use Ricklab\Location\Calculator\DistanceCalculator;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * @implements IteratorAggregate<LineString>
 */
class Polygon implements GeometryInterface, IteratorAggregate
{
    /** @use GeometryTrait<LineString> */
    use GeometryTrait;

    /**
     * @var LineString[]
     */
    private array $geometries = [];

    public static function getWktType(): string
    {
        return 'POLYGON';
    }

    public static function getGeoJsonType(): string
    {
        return 'Polygon';
    }

    public static function fromArray(array $geometries): self
    {
        $result = [];
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
        foreach ($lines as $line) {
            $this->add($line);
        }
    }

    /**
     * The length of the perimeter of the outer-most polygon in unit specified.
     *
     * @param string                  $unit       defaults to "meters"
     * @param DistanceCalculator|null $calculator The calculator that is used for calculating the distance. If null, uses DefaultDistanceCalculator.
     */
    public function getPerimeter(string $unit = UnitConverter::UNIT_METERS, DistanceCalculator $calculator = null): float
    {
        return $this->geometries[0]->getLength($unit, $calculator);
    }

    public function getBBox(): BoundingBox
    {
        return BoundingBox::fromGeometry($this);
    }

    /**
     * @return LineString[]
     */
    public function getLineStrings(): array
    {
        return $this->geometries;
    }

    private function add(LineString $lineString): void
    {
        if (!$lineString->isClosedShape()) {
            $lineString = $lineString->addPoint($lineString->getFirst());
        }

        $this->geometries[] = $lineString;
    }

    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }
}
