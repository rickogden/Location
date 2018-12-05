<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry\Traits;

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
        return self::getWktType().$this;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => self::getGeoJsonType(),
            'coordinates' => $this->toArray(),
        ];
    }
}
