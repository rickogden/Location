<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 10:07.
 */

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Converter\UnitConverter;

abstract class Ellipsoid implements EllipsoidInterface
{
    abstract protected static function getRadiusInMeters(): float;

    abstract protected static function getMinorSemiAxisInMeters(): float;

    abstract protected static function getMajorSemiAxisInMeters(): float;

    /**
     * Returns the average radius of the ellipsoid in specified units.
     *
     * @param string $unit can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     */
    public static function radius(string $unit = UnitConverter::UNIT_METERS): float
    {
        return UnitConverter::convertFromMeters(static::getRadiusInMeters(), $unit);
    }

    /**
     * @param string $unit The unit you want the multiplier of
     *
     * @return float The multiplier
     *
     * @deprecated use UnitConverter::getMultiplier();
     */
    public static function getMultiplier(string $unit = UnitConverter::UNIT_METERS): float
    {
        return UnitConverter::getMultiplier($unit);
    }

    /**
     * @param string $unit unit of measurement
     */
    public static function getMajorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float
    {
        return UnitConverter::convertFromMeters(static::getMajorSemiAxisInMeters(), $unit);
    }

    /**
     * @param string $unit unit of measurement
     */
    public static function getMinorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float
    {
        return UnitConverter::convertFromMeters(static::getMinorSemiAxisInMeters(), $unit);
    }

    public static function getFlattening(): float
    {
        return (static::getMajorSemiAxisInMeters() - static::getMinorSemiAxisInMeters()) / static::getMajorSemiAxisInMeters();
    }
}
