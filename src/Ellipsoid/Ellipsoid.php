<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Converter\UnitConverter;

/**
 * @psalm-immutable
 */
abstract class Ellipsoid implements EllipsoidInterface
{
    abstract protected static function getRadiusInMeters(): float;

    abstract protected static function getMinorSemiAxisInMeters(): float;

    abstract protected static function getMajorSemiAxisInMeters(): float;

    public static function radius(string $unit = UnitConverter::UNIT_METERS): float
    {
        return UnitConverter::convertFromMeters(static::getRadiusInMeters(), $unit);
    }

    public static function getMajorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float
    {
        return UnitConverter::convertFromMeters(static::getMajorSemiAxisInMeters(), $unit);
    }

    public static function getMinorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float
    {
        return UnitConverter::convertFromMeters(static::getMinorSemiAxisInMeters(), $unit);
    }

    public static function getFlattening(): float
    {
        return (static::getMajorSemiAxisInMeters() - static::getMinorSemiAxisInMeters()) / static::getMajorSemiAxisInMeters();
    }
}
