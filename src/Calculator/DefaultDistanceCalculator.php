<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

final class DefaultDistanceCalculator implements DistanceCalculatorInterface
{
    private static ?DistanceCalculatorInterface $defaultCalculator = null;

    public static function setDefaultCalculator(DistanceCalculatorInterface $distanceCalculator): void
    {
        self::$defaultCalculator = $distanceCalculator;
    }

    public static function getDefaultCalculator(): DistanceCalculatorInterface
    {
        return self::$defaultCalculator ?? new HaversineCalculator();
    }

    public static function calculate(Point $point1, Point $point2, EllipsoidInterface $ellipsoid): float
    {
        return self::getDefaultCalculator()::calculate($point1, $point2, $ellipsoid);
    }
}
