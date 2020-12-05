<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry\Traits;

use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Transformer\WktTransformer;

/**
 * @author Rick Ogden <rick@rickogden.com>
 */
trait TransformationTrait
{
    abstract public static function getWktType(): string;

    abstract public static function getGeoJsonType(): string;

    abstract public function __toString(): string;

    abstract public function toArray(): array;

    public function toWkt(): string
    {
        if (!$this instanceof GeometryInterface) {
            throw new \LogicException('Cannot convert non-geometry to WKT.');
        }

        return WktTransformer::encode($this);
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => static::getGeoJsonType(),
            'coordinates' => $this->toArray(),
        ];
    }
}
