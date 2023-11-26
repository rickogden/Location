<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Converter\UnitConverter;

interface EllipsoidInterface
{
    /**
     * Returns the average radius of the ellipsoid in specified units.
     *
     * @param string $unit can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     *
     * @psalm-param  UnitConverter::UNIT_* $unit
     */
    public function radius(string $unit = UnitConverter::UNIT_METERS): float|int;

    /**
     * @param string $unit unit of measurement
     *
     * @psalm-param  UnitConverter::UNIT_* $unit unit of measurement
     */
    public function majorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float|int;

    /**
     * @param UnitConverter::UNIT_* $unit unit of measurement
     */
    public function minorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float|int;

    public function flattening(): float|int;
}
