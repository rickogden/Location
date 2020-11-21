<?php

declare(strict_types=1);

namespace Ricklab\Location\Psalm\Calculator;

use Ricklab\Location\Calculator\BearingCalculator;
use PHPUnit\Framework\TestCase;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Location;

class BearingCalculatorTest extends TestCase
{
    public function testCalculateFinalBearing(): void
    {
        $point1 = new Point(0.119, 52.205);
        $point2 = new Point(2.351, 48.857);
        Location::$useSpatialExtension = false;
        $finalBearing = BearingCalculator::calculateFinalBearing($point1, $point2);
        $this->assertSame(157.9, \round($finalBearing, 1));
        Location::$useSpatialExtension = true;
        $finalBearing = BearingCalculator::calculateFinalBearing($point1, $point2);
        $this->assertSame(157.9, \round($finalBearing, 1));
    }
}
