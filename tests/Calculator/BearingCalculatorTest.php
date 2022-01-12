<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Geometry\Point;

class BearingCalculatorTest extends TestCase
{
    public function testCalculateFinalBearing(): void
    {
        $point1 = new Point(0.119, 52.205);
        $point2 = new Point(2.351, 48.857);
        BearingCalculator::disableGeoSpatialExtension();
        $finalBearing = BearingCalculator::calculateFinalBearing($point1, $point2);
        $this->assertSame(157.9, round($finalBearing, 1));
        BearingCalculator::enableGeoSpatialExtension();
        $finalBearing = BearingCalculator::calculateFinalBearing($point1, $point2);
        $this->assertSame(157.9, round($finalBearing, 1));
    }
}
