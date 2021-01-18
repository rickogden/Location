<?php

declare(strict_types=1);

namespace Ricklab\Location\Psalm\Calculator;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Calculator\EquirectangleCalculator;
use Ricklab\Location\Calculator\VincentyCalculator;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Ellipsoid\DefaultEllipsoid;
use Ricklab\Location\Geometry\Point;

class VincentyCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        VincentyCalculator::disableGeoSpatialExtension();
    }

    public static function distanceProvider(): \Generator
    {
        yield [new Point(-2.23194, 53.48204), new Point(-2.27354, 53.48575), 2792.28039];
        yield [new Point(-2.1670963, 53.4123682), new Point(-2.1574967, 53.4112062), 651.31018];
        yield [new Point(144.42486788888888, -37.95103341666667), new Point(143.92649552777777, -37.65282113888889), 54972.27114];
    }

    /**
     * @dataProvider distanceProvider
     */
    public function testCalculate(Point $point1, Point $point2, float $distance): void
    {
        $this->assertSame($distance, \round($point1->distanceTo($point2, UnitConverter::UNIT_METERS, new VincentyCalculator()), 5));
        $this->assertSame($distance, \round($point2->distanceTo($point1, UnitConverter::UNIT_METERS, new VincentyCalculator()), 5));
    }
}
