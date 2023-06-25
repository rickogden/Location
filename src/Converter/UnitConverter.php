<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

use Exception;
use InvalidArgumentException;

final class UnitConverter
{
    public const UNIT_FEET = 'feet';
    public const UNIT_KM = 'km';
    public const UNIT_METERS = 'meters';
    public const UNIT_MILES = 'miles';
    public const UNIT_NAUTICAL_MILES = 'nautical miles';
    public const UNIT_YARDS = 'yards';

    public const MULTIPLIERS = [
        self::UNIT_KM => 0.001,
        self::UNIT_MILES => 0.00062137119,
        self::UNIT_METERS => 1,
        self::UNIT_FEET => 3.2808399,
        self::UNIT_YARDS => 1.0936133,
        self::UNIT_NAUTICAL_MILES => 0.0005399568,
    ];

    public const KEYS = [
        'km' => self::UNIT_KM,
        'kilometres' => self::UNIT_KM,
        'kilometers' => self::UNIT_KM,
        'miles' => self::UNIT_MILES,
        'metres' => self::UNIT_METERS,
        'meters' => self::UNIT_METERS,
        'm' => self::UNIT_METERS,
        'feet' => self::UNIT_FEET,
        'ft' => self::UNIT_FEET,
        'foot' => self::UNIT_FEET,
        'yards' => self::UNIT_YARDS,
        'yds' => self::UNIT_YARDS,
        'nautical miles' => self::UNIT_NAUTICAL_MILES,
        'nm' => self::UNIT_NAUTICAL_MILES,
    ];

    public static function convert(float $distance, string $from, string $to): float
    {
        $m = $distance / self::getMultiplier($from);

        return $m * self::getMultiplier($to);
    }

    /**
     * A micro-optimised static method for converting from meters.
     */
    public static function convertFromMeters(float $distance, string $to): float
    {
        return $distance * self::getMultiplier($to);
    }

    /**
     * @param string $unit The unit you want the multiplier of
     *
     * @return float The multiplier
     */
    public static function getMultiplier(string $unit = self::UNIT_METERS): float
    {
        try {
            return self::MULTIPLIERS[self::KEYS[mb_strtolower($unit)]];
        } catch (Exception $e) {
            throw new InvalidArgumentException('Unit '.$unit.' is not a recognised unit.', 0, $e);
        }
    }
}
