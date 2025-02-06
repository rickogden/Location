<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use function extension_loaded;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Ellipsoid\DefaultEllipsoid;
use Ricklab\Location\Geometry\Point;

class VincentyCalculatorTest extends TestCase
{
    private Point $buninyond;
    private Point $flinders;

    protected function setUp(): void
    {
        $this->flinders = new Point(144.42486788888888, -37.95103341666667);

        $this->buninyond = new Point(143.92649552777777, -37.65282113888889);
    }

    public function testCalculateDistance(): void
    {
        $vincentyCalculator = new VincentyCalculator(false);
        $this->assertSame(54972.271, round($vincentyCalculator->calculateDistance($this->flinders, $this->buninyond, DefaultEllipsoid::get()), 3));
    }

    public function testCalculateDistanceWithGeoSpatialExtension(): void
    {
        if (!extension_loaded('geospatial')) {
            $this->markTestSkipped('The geospatial extension is not available.');
        }

        $vincentyCalculator = new VincentyCalculator(true);
        $this->assertSame(54972.271, round($vincentyCalculator->calculateDistance($this->flinders, $this->buninyond, DefaultEllipsoid::get()), 3));
    }
}
