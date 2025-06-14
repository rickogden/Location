<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Converter\Unit;
use Ricklab\Location\Converter\NativeUnitConverter;

interface EllipsoidInterface
{
    /**
     * @return float|numeric-string
     */
    public function radius(Unit $unit = Unit::METERS): float|string;

    /**
     * @param Unit $unit unit of measurement
     *
     * @return float|numeric-string
     */
    public function majorSemiAxis(Unit $unit = Unit::METERS): float|string;

    /**
     * @param Unit $unit unit of measurement
     *
     * @return float|numeric-string
     */
    public function minorSemiAxis(Unit $unit = Unit::METERS): float|string;

    /**
     * @return float|numeric-string
     */
    public function flattening(): float|string;

    public function equals(Ellipsoid $ellipsoid): bool;
}
