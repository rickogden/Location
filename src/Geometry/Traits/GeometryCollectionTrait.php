<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry\Traits;

use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\Point;

/**
 * @author Rick Ogden <rick@airtimerewards.com>
 */
trait GeometryCollectionTrait
{
    /**
     * @var GeometryInterface[]
     */
    protected $geometries = [];

    abstract public static function getWktType(): string;

    abstract public static function getGeoJsonType(): string;

    /**
     * @return Point[]
     */
    public function getPoints(): array
    {
        $points = [];
        foreach ($this->geometries as $geometry) {
            $linePoints = $geometry->getPoints();
            $points[] = $linePoints;
        }

        return \array_merge(...$points);
    }

    public function __toString(): string
    {
        return '('.\implode(',', $this->geometries).')';
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => self::getGeoJsonType(),
            'coordinates' => $this->toArray(),
        ];
    }

    public function toArray(): array
    {
        $return = [];
        foreach ($this->geometries as $line) {
            $return[] = $line->toArray();
        }

        return $return;
    }

    public function toWkt(): string
    {
        return self::getWktType().$this;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->getGeometries());
    }
}
