<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

interface FractionAlongLineCalculator
{
    public function calculateFractionAlongLine(
        Point $point1,
        Point $point2,
        float $fraction,
        DistanceCalculator $calculator,
        EllipsoidInterface $ellipsoid,
    ): Point;
}
