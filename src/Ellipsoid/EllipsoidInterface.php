<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Converter\UnitConverter;

/**
 * @psalm-immutable
 */
interface EllipsoidInterface
{
    /**
     * Returns the average radius of the ellipsoid in specified units.
     *
     * @param string $unit can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     *
     * @psalm-param  UnitConverter::UNIT_* $unit
     */
    public static function radius(string $unit = UnitConverter::UNIT_METERS): float;

    /**
     * @param string $unit unit of measurement
     *
     * @psalm-param  UnitConverter::UNIT_* $unit unit of measurement
     */
    public static function getMajorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float;

    /**
     * @param string $unit unit of measurement
     *
     * @psalm-param  UnitConverter::UNIT_* $unit unit of measurement
     */
    public static function getMinorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float;

    public static function getFlattening(): float;
}
