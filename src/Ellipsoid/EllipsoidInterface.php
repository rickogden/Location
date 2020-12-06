<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Converter\UnitConverter;

interface EllipsoidInterface
{
    /**
     * @var array Unit multipliers relative to km
     *
     * @deprecated use UnitConverter::MULTIPLIERS
     */
    public const MULTIPLIERS = UnitConverter::MULTIPLIERS;

    /**
     * @var array Key translations for multipliers
     *
     * @deprecated use UnitConverter::KEYS;
     */
    public const KEYS = UnitConverter::KEYS;

    public static function radius(string $unit = UnitConverter::UNIT_METERS): float;

    public static function getMultiplier(string $unit = UnitConverter::UNIT_METERS): float;

    public static function getMajorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float;

    public static function getMinorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float;

    public static function getFlattening(): float;
}
