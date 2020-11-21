<?php

declare(strict_types=1);

namespace Ricklab\Location\Psalm\Calculator;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Calculator\VincentyCalculator;
use Ricklab\Location\Location;
use Ricklab\Location\Geometry\Point;

class VincentyCalculatorTest extends TestCase
{

    public function testCalculate(): void
    {
        $flinders = new Point(144.42486788888888, -37.95103341666667);

        $buninyond = new Point(143.92649552777777, -37.65282113888889);

        Location::$useSpatialExtension = true;
        $this->assertSame(54972.271, \round(VincentyCalculator::calculate($flinders, $buninyond, Location::getEllipsoid()), 3));

        Location::$useSpatialExtension = false;
        $this->assertSame(54972.271, \round(VincentyCalculator::calculate($flinders, $buninyond, Location::getEllipsoid()), 3));
        Location::$useSpatialExtension = true;
    }
}
