<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use function extension_loaded;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Geometry\Point;

#[CoversClass(DefaultBearingCalculator::class)]
class DefaultBearingCalculatorTest extends TestCase
{
    private Point $point1;
    private Point $point2;

    protected function setUp(): void
    {
        $this->point1 = new Point(0.119, 52.205);
        $this->point2 = new Point(2.351, 48.857);
    }

    public function testCalculateFinalBearing(): void
    {
        $bearingCalculator = new DefaultBearingCalculator($this->createMock(UnitConverter::class), false);
        $finalBearing = $bearingCalculator->calculateFinalBearing($this->point1, $this->point2);
        $this->assertSame(157.9, round($finalBearing, 1));
    }

    public function testCalculateFinalBearingWithExtension(): void
    {
        if (!extension_loaded('geospatial')) {
            $this->markTestSkipped('The geospatial extension is not available.');
        }

        $bearingCalculator = new DefaultBearingCalculator($this->createMock(UnitConverter::class), true);
        $finalBearing = $bearingCalculator->calculateFinalBearing($this->point1, $this->point2);
        $this->assertSame(157.9, round($finalBearing, 1));
    }
}
