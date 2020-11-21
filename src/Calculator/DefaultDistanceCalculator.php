<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

final class DefaultDistanceCalculator implements DistanceCalculator
{
    private static ?DistanceCalculator $defaultCalculator = null;

    public static function setDefaultCalculator(DistanceCalculator $distanceCalculator): void
    {
        self::$defaultCalculator = $distanceCalculator;
    }

    public static function getDefaultCalculator(): DistanceCalculator
    {
        return self::$defaultCalculator ?? new HaversineCalculator();
    }

    public static function calculate(Point $point1, Point $point2, EllipsoidInterface $ellipsoid): float
    {
        return self::getDefaultCalculator()::calculate($point1, $point2, $ellipsoid);
    }
}
