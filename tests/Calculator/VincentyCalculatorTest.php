<?php

declare(strict_types=1);

namespace Ricklab\Location\Psalm\Calculator;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Calculator\VincentyCalculator;
use Ricklab\Location\Ellipsoid\DefaultEllipsoid;
use Ricklab\Location\Geometry\Point;

class VincentyCalculatorTest extends TestCase
{
    public function testCalculate(): void
    {
        $flinders = new Point(144.42486788888888, -37.95103341666667);

        $buninyond = new Point(143.92649552777777, -37.65282113888889);

        VincentyCalculator::enableGeoSpatialExtension();
        $this->assertSame(54972.271, \round(VincentyCalculator::calculate($flinders, $buninyond, DefaultEllipsoid::get()), 3));

        VincentyCalculator::disableGeoSpatialExtension();
        $this->assertSame(54972.271, \round(VincentyCalculator::calculate($flinders, $buninyond, DefaultEllipsoid::get()), 3));
        VincentyCalculator::enableGeoSpatialExtension();
    }
}
