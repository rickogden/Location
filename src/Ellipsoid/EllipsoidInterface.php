<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Location;

/**
 * @author Rick Ogden <rick@rickogden.com>
 */
interface EllipsoidInterface
{

    /**
     * @var array Unit multipliers relative to km
     */
    public const MULTIPLIERS = [
        Location::UNIT_KM => 0.001,
        Location::UNIT_MILES => 0.00062137119,
        Location::UNIT_METRES => 1,
        Location::UNIT_FEET => 3.2808399,
        Location::UNIT_YARDS => 1.0936133,
        Location::UNIT_NAUTICAL_MILES => 0.0005399568,
    ];

    /**
     * @var array Key translations for multipliers
     */
    public const KEYS = [
        'km' => Location::UNIT_KM,
        'kilometres' => Location::UNIT_KM,
        'kilometers' => Location::UNIT_KM,
        'miles' => Location::UNIT_MILES,
        'metres' => Location::UNIT_METRES,
        'meters' => Location::UNIT_METRES,
        'm' => Location::UNIT_METRES,
        'feet' => Location::UNIT_FEET,
        'ft' => Location::UNIT_FEET,
        'foot' => Location::UNIT_FEET,
        'yards' => Location::UNIT_YARDS,
        'yds' => Location::UNIT_YARDS,
        'nautical miles' => Location::UNIT_NAUTICAL_MILES,
        'nm' => Location::UNIT_NAUTICAL_MILES,
    ];

    public function radius(string $unit = Location::UNIT_METRES): float;

    public function getMultiplier(string $unit = Location::UNIT_METRES): float;

    public function getMajorSemiAxis(string $unit = Location::UNIT_METRES): float;

    public function getMinorSemiAxis(string $unit = Location::UNIT_METRES): float;

    public function getFlattening(): float;

}
