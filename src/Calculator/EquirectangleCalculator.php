<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

final class EquirectangleCalculator implements DistanceCalculator
{
    public const FORMULA = 'EQUIRECTANGLE';

    public static function calculate(Point $point1, Point $point2, EllipsoidInterface $ellipsoid): float
    {
        $lat1 = $point1->latitudeToRad();
        $lat2 = $point2->latitudeToRad();
        $lon1 = $point1->longitudeToRad();
        $lon2 = $point2->longitudeToRad();
        $x = ($lon2 - $lon1) * \cos(($lat1 + $lat2) / 2);
        $y = $lat2 - $lat1;
        $d = \sqrt($x ** 2 + $y ** 2);

        return $d * $ellipsoid::radius();
    }

    public static function formula(): string
    {
        return self::FORMULA;
    }
}
