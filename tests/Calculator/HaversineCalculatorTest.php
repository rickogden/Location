<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Geometry\Point;

class HaversineCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        HaversineCalculator::disableGeoSpatialExtension();
    }

    public static function distanceProvider(): \Generator
    {
        yield [new Point(-2.23194, 53.48204), new Point(-2.27354, 53.48575), 2783.2685];
        yield [new Point(-2.1670963, 53.4123682), new Point(-2.1574967, 53.4112062), 649.23819];
        yield [new Point(144.42486788888888, -37.95103341666667), new Point(143.92649552777777, -37.65282113888889), 54925.50949];
    }

    /**
     * @dataProvider distanceProvider
     */
    public function testCalculate(Point $point1, Point $point2, float $distance): void
    {
        $this->assertSame($distance, \round($point1->distanceTo($point2, UnitConverter::UNIT_METERS, new HaversineCalculator()), 5));
        $this->assertSame($distance, \round($point2->distanceTo($point1, UnitConverter::UNIT_METERS, new HaversineCalculator()), 5));
    }
}
