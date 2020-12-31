<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

final class DefaultDistanceCalculator implements DistanceCalculator, UsesGeoSpatialExtensionInterface
{
    private static ?DistanceCalculator $defaultCalculator = null;

    public static function setDefaultCalculator(DistanceCalculator $distanceCalculator): void
    {
        if ($distanceCalculator instanceof self) {
            throw new \InvalidArgumentException('Cannot pass an instance of DefaultDistanceCalculator in as a default calculator.');
        }

        self::$defaultCalculator = $distanceCalculator;
    }

    public static function getDefaultCalculator(): DistanceCalculator
    {
        if (null === self::$defaultCalculator) {
            self::$defaultCalculator = new HaversineCalculator();
        }

        return self::$defaultCalculator;
    }

    public static function calculate(Point $point1, Point $point2, EllipsoidInterface $ellipsoid): float
    {
        return self::getDefaultCalculator()::calculate($point1, $point2, $ellipsoid);
    }

    public static function formula(): string
    {
        return self::getDefaultCalculator()::formula();
    }

    public static function enableGeoSpatialExtension(): void
    {
        $calculator = self::getDefaultCalculator();

        if ($calculator instanceof UsesGeoSpatialExtensionInterface) {
            $calculator::enableGeoSpatialExtension();
        }
    }

    public static function disableGeoSpatialExtension(): void
    {
        $calculator = self::getDefaultCalculator();

        if ($calculator instanceof UsesGeoSpatialExtensionInterface) {
            $calculator::disableGeoSpatialExtension();
        }
    }
}
