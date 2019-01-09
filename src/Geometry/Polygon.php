<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Geometry\Traits\GeometryTrait;
use Ricklab\Location\Location;

class Polygon implements GeometryInterface, \IteratorAggregate
{
    use GeometryTrait;

    /**
     * @var LineString[]
     */
    protected $geometries = [];

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
     * Pass in an array of Points to create a Polygon or multiple arrays of points for a Polygon with holes in.
     *
     * @param LineString[]
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
     * @param string   $unit
     * @param int|null $formula defaults to Location::$defaultFormula
     */
    public function getPerimeter($unit = 'km', $formula = null): float
    {
        return $this->geometries[0]->getLength($unit, $formula);
    }

    public function getBBox(): self
    {
        return Location::getBBox($this);
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
}
