<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

interface DistanceCalculator
{
    /**
     * @return float the distance in metres
     */
    public static function calculateDistance(Point $point1, Point $point2, EllipsoidInterface $ellipsoid): float;

    public static function formula(): string;
}
