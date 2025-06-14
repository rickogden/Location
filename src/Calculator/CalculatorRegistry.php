<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Converter\UnitConverterRegistry;

final class CalculatorRegistry
{
    private static ?DistanceCalculator $distanceCalculator = null;
    private static ?BearingCalculator $bearingCalculator = null;
    private static ?FractionAlongLineCalculator $fractionAlongLineCalculator = null;
    private static bool $geoSpatialExtensionEnabled = true;

    public static function getDistanceCalculator(): DistanceCalculator
    {
        if (null === self::$distanceCalculator) {
            self::$distanceCalculator = new HaversineCalculator(self::$geoSpatialExtensionEnabled);
        }

        return self::$distanceCalculator;
    }

    public static function getBearingCalculator(): BearingCalculator
    {
        if (null === self::$bearingCalculator) {
            self::$bearingCalculator = new DefaultBearingCalculator(
                UnitConverterRegistry::getUnitConverter(),
                self::$geoSpatialExtensionEnabled
            );
        }

        return self::$bearingCalculator;
    }

    public static function getFractionAlongLineCalculator(): FractionAlongLineCalculator
    {
        if (null === self::$fractionAlongLineCalculator) {
            self::$fractionAlongLineCalculator = new DefaultFractionAlongLineCalculator(
                self::getDistanceCalculator(),
                self::$geoSpatialExtensionEnabled,
            );
        }

        return self::$fractionAlongLineCalculator;
    }

    public static function setDistanceCalculator(DistanceCalculator $calculator): void
    {
        self::$distanceCalculator = $calculator;
    }

    public static function setBearingCalculator(BearingCalculator $calculator): void
    {
        self::$bearingCalculator = $calculator;
    }

    public static function setFractionAlongLineCalculator(FractionAlongLineCalculator $calculator): void
    {
        self::$fractionAlongLineCalculator = $calculator;
    }

    public static function enableGeoSpatialExtension(): void
    {
        if (self::$geoSpatialExtensionEnabled) {
            return;
        }

        self::$geoSpatialExtensionEnabled = true;

        foreach ([self::$distanceCalculator, self::$bearingCalculator, self::$fractionAlongLineCalculator] as $calculator) {
            if ($calculator instanceof UsesGeoSpatialExtensionInterface) {
                $calculator->enableGeoSpatialExtension();
            }
        }
    }

    public static function disableGeoSpatialExtension(): void
    {
        if (!self::$geoSpatialExtensionEnabled) {
            return;
        }
        self::$geoSpatialExtensionEnabled = false;
        foreach ([self::$distanceCalculator, self::$bearingCalculator, self::$fractionAlongLineCalculator] as $calculator) {
            if ($calculator instanceof UsesGeoSpatialExtensionInterface) {
                $calculator->disableGeoSpatialExtension();
            }
        }
    }
}
