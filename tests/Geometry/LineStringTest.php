<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Converter\Unit;
use function extension_loaded;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ricklab\Location\Calculator\CalculatorRegistry;
use Ricklab\Location\Calculator\VincentyCalculator;
use Ricklab\Location\Converter\NativeUnitConverter;

class LineStringTest extends TestCase
{
    protected LineString $line;

    protected function setUp(): void
    {
        $point1 = new Point(-2.27354, 53.48575);
        $point2 = new Point(-2.23194, 53.48204);
        $this->line = new LineString([$point1, $point2]);
    }

    public function testStatic(): void
    {
        $point1 = new Point(-2.27354, 53.48575);
        $point2 = new Point(-2.23194, 53.48204);
        $line = LineString::fromArray([$point1, $point2]);

        $line2 = LineString::fromArray([$point1->toArray(), $point2->toArray()]);

        $this->assertInstanceOf(LineString::class, $line);
        $this->assertInstanceOf(LineString::class, $line2);
    }

    public function testOnePointInArrayException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $point1 = new Point(-2.27354, 53.48575);

        $line = new LineString([$point1]);
    }

    public function testGetLength(): void
    {
        $this->assertEquals(2.783, round($this->line->getLength(Unit::KM), 3));
        $this->assertEquals(2.792, round($this->line->getLength(Unit::KM, new VincentyCalculator()), 3));
    }

    public function testInitialBearing(): void
    {
        CalculatorRegistry::disableGeoSpatialExtension();
        $this->assertEquals(98.50702, round($this->line->getInitialBearing(), 5));
    }

    public function testInitialBearingWithExtension(): void
    {
        if (!extension_loaded('geospatial')) {
            $this->markTestSkipped('The geospatial extension is not available.');
        }
        CalculatorRegistry::enableGeoSpatialExtension();
        $this->assertEquals(98.50702, round($this->line->getInitialBearing(), 5));
    }

    public function testGeoJson(): void
    {
        $retval = json_encode($this->line);
        $this->assertEquals('{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204]]}', $retval);
    }

    public function testToArray(): void
    {
        $retval = [[-2.27354, 53.48575], [-2.23194, 53.48204]];

        $this->assertEquals($retval, $this->line->toArray());
    }

    public function testToString(): void
    {
        $retval = '(-2.27354 53.48575, -2.23194 53.48204)';
        $this->assertEquals($retval, (string) $this->line);
    }

    public function testBBox(): void
    {
        $this->assertJsonStringEqualsJsonString(
            '{"type":"Polygon","coordinates":[[[-2.27354,53.48575],[-2.23194,53.48575],[-2.23194,53.48204],[-2.27354,53.48204],[-2.27354,53.48575]]]}',
            json_encode($this->line->getBBox()->getPolygon())
        );
    }

    public function testFromArray(): void
    {
        $line = LineString::fromArray([[-2.27354, 53.48575], [-2.23194, 53.48204]]);

        $point1 = $line->getFirst();
        $point2 = $line->getLast();

        $this->assertEquals(-2.27354, $point1->getLongitude());
        $this->assertEquals(53.48204, $point2->getLatitude());
    }

    public function testReverse(): void
    {
        $original = [[-2.27354, 53.48575], [-2.23194, 53.48204]];

        $lineString = LineString::fromArray($original);
        $lineString2 = $lineString->reverse();
        $this->assertEquals($original[0], $lineString2->toArray()[1]);
        $this->assertEquals($original[1], $lineString2->toArray()[0]);
        $this->assertEquals($original[0], $lineString->toArray()[0]);
        $this->assertEquals($original[1], $lineString->toArray()[1]);
    }

    public static function equalProvider(): Generator
    {
        $original = [[-2.27354, 53.48575], [-2.23194, 53.48204]];

        $lineString = LineString::fromArray($original);
        $lineString2 = LineString::fromArray($original);
        yield 'Different objects' => [$lineString, $lineString2];
        yield 'Different objects reversed' => [$lineString2, $lineString];
        yield 'Same object' => [$lineString, $lineString];
    }

    #[DataProvider('equalProvider')]
    public function testEqualsIsTrue(LineString $lineString, LineString $lineString2): void
    {
        $this->assertTrue($lineString->equals($lineString2));
    }

    public function testNotEquals(): void
    {
        $original = [[-2.27354, 53.48575], [-2.23194, 53.48204]];

        $lineString = LineString::fromArray($original);
        $lineString2 = $lineString->reverse();
        $this->assertFalse($lineString->equals($lineString2));

        $newGeom = $original;
        $newGeom[] = [-2.23124, 53.48214];
        $lineString2 = LineString::fromArray($newGeom);
        $this->assertFalse($lineString->equals($lineString2));

        $polygon = new Polygon([$lineString]);
        $this->assertFalse($lineString->equals($polygon));

        $collection = new MultiPoint($lineString->getPoints());
        $this->assertFalse($lineString->equals($collection));
    }

    public function testGetFirst(): void
    {
        $firstPoint = $this->line->getFirst();
        $this->assertInstanceOf(Point::class, $firstPoint);
        $this->assertEquals(-2.27354, $firstPoint->getLongitude());
        $this->assertEquals(53.48575, $firstPoint->getLatitude());
    }

    public function testGetLast(): void
    {
        $lastPoint = $this->line->getLast();
        $this->assertInstanceOf(Point::class, $lastPoint);
        $this->assertEquals(-2.23194, $lastPoint->getLongitude());
        $this->assertEquals(53.48204, $lastPoint->getLatitude());
    }

    public function testGetPoints(): void
    {
        $points = $this->line->getPoints();
        $this->assertCount(2, $points);
        $this->assertInstanceOf(Point::class, $points[0]);
        $this->assertInstanceOf(Point::class, $points[1]);
    }

    public function testAddPoint(): void
    {
        $newPoint = new Point(-2.20000, 53.40000);
        $newLine = $this->line->addPoint($newPoint);
        $this->assertCount(3, $newLine->getPoints());
        $this->assertEquals($newPoint, $newLine->getLast());
    }

    public function testWithPoint(): void
    {
        $newPoint = new Point(-2.20000, 53.40000);
        $newLine = $this->line->withPoint($newPoint);
        $this->assertCount(3, $newLine->getPoints());
        $this->assertEquals($newPoint, $newLine->getLast());
    }

    public function testGetClosedShape(): void
    {
        $closedLine = $this->line->getClosedShape();
        $this->assertTrue($closedLine->isClosedShape());
        $this->assertEquals($closedLine->getFirst(), $closedLine->getLast());
    }

    public function testIsClosedShape(): void
    {
        $this->assertFalse($this->line->isClosedShape());
        $closedLine = $this->line->getClosedShape();
        $this->assertTrue($closedLine->isClosedShape());
    }

    public function testContains(): void
    {
        $point = new Point(-2.27354, 53.48575);
        $this->assertTrue($this->line->contains($point));
        $pointNotInLine = new Point(-2.20000, 53.40000);
        $this->assertFalse($this->line->contains($pointNotInLine));
    }
}
