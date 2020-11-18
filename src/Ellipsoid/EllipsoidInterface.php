<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Calculator\UnitConverter;
use Ricklab\Location\Location;

/**
 * @author Rick Ogden <rick@rickogden.com>
 */
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

    public function radius(string $unit = Location::UNIT_METRES): float;

    public function getMultiplier(string $unit = Location::UNIT_METRES): float;

    public function getMajorSemiAxis(string $unit = Location::UNIT_METRES): float;

    public function getMinorSemiAxis(string $unit = Location::UNIT_METRES): float;

    public function getFlattening(): float;
}
