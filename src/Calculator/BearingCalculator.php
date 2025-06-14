<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Converter\Unit;
use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

interface BearingCalculator
{
    /**
     * @return float|numeric-string
     */
    public function calculateFinalBearing(Point $point1, Point $point2): float|string;

    /**
     * @return float|numeric-string
     */
    public function calculateInitialBearing(Point $point1, Point $point2): float|string;

    public function calculateRelativePoint(
        EllipsoidInterface $ellipsoid,
        Point $point,
        float|string $distance,
        float|string $bearing,
        Unit $unit = Unit::METERS,
    ): Point;
}
