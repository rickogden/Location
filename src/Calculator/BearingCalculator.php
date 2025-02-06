<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Geometry\Point;

interface BearingCalculator
{
    public function calculateInitialBearing(Point $point1, Point $point2): float;

    public function calculateFinalBearing(Point $point1, Point $point2): float;
}
