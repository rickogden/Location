<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 16/07/15
 * Time: 16:34.
 */

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * Class MultiLineString.
 */
class MultiLineString implements GeometryInterface, GeometryCollectionInterface, \IteratorAggregate
{
    use GeometryTrait;

    /**
     * @var LineString[]
     */
    protected array $geometries = [];

    public static function getGeoJsonType(): string
    {
        return 'MultiLineString';
    }

    public static function getWktType(): string
    {
        return 'MULTILINESTRING';
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

    public function __construct(array $lineStrings)
    {
        foreach ($lineStrings as $lineString) {
            $this->addGeometry($lineString);
        }
    }

    /**
     * @return LineString[] an array of the LineStrings
     */
    public function getGeometries(): array
    {
        return $this->geometries;
    }

    /**
     * Adds a new LineString to the collection.
     */
    public function addGeometry(LineString $lineString): void
    {
        $this->geometries[] = $lineString;
    }

    /**
     * Removes a LineString from the collection.
     */
    public function removeGeometry(LineString $lineString): void
    {
        foreach ($this->geometries as $index => $geom) {
            if ($lineString === $geom) {
                unset($this->geometries[$index]);
            }
        }
    }

    protected function getGeometryArray(): array
    {
        return $this->geometries;
    }
}
