<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

/**
 * @enum Unit<numeric-string>
 */
enum Unit: string
{
    private const STRING_REPS = [
        'KM' => self::KM,
        'KILOMETERS' => self::KM,
        'MILES' => self::MILES,
        'METERS' => self::METERS,
        'M' => self::METERS,
        'FEET' => self::FEET,
        'FTS' => self::FEET,
        'YARDS' => self::YARDS,
        'YDS' => self::YARDS,
        'NAUTICAL_MILES' => self::NAUTICAL_MILES,
        'NM' => self::NAUTICAL_MILES,
    ];

    case KM = '0.001';
    case MILES = '0.00062137119';
    case METERS = '1';
    case FEET = '3.2808399';
    case YARDS = '1.0936133';
    case NAUTICAL_MILES = '0.0005399568';

    /**
     * @param float|numeric-string $distance
     *
     * @return float|numeric-string
     */
    public static function convert(float|string $distance, Unit $from, Unit $to): float|string
    {
        return UnitConverterRegistry::getUnitConverter()->convert($distance, $from, $to);
    }

    /**
     * A micro-optimised static method for converting from meters.
     *
     * @param float|numeric-string $distance
     *
     * @return float|numeric-string
     */
    public function fromMeters(float|string $distance): float|string
    {
        return UnitConverterRegistry::getUnitConverter()->convertFromMeters($distance, $this);
    }

    /**
     * @param float|numeric-string $distance
     *
     * @return float|numeric-string
     */
    public function toMeters(float|string $distance): float|string
    {
        return UnitConverterRegistry::getUnitConverter()->convert($distance, $this, Unit::METERS);
    }

    public static function fromString(string $unit): ?self
    {
        return self::STRING_REPS[strtoupper($unit)] ?? null;
    }
}
