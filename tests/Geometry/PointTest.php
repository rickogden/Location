<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ricklab\Location\Calculator\DefaultDistanceCalculator;
use Ricklab\Location\Calculator\VincentyCalculator;
use Ricklab\Location\Converter\DegreesMinutesSeconds;
use Ricklab\Location\Converter\UnitConverter;

class PointTest extends TestCase
{
    public Point $point;
    public float $lat = 53.48575;
    public float $lon = -2.27354;

    protected function setUp(): void
    {
        $this->point = new Point($this->lon, $this->lat);
    }

    public function testInstanceOfClassIsAPoint(): void
    {
        $this->assertInstanceOf(Point::class, $this->point);
    }

    public function testPointCreationAsArray(): void
    {
        $point = Point::fromArray([$this->lon, $this->lat]);
        $this->assertEquals($this->lat, $point->getLatitude());
        $this->assertEquals($this->lon, $point->getLongitude());
    }

    public function testLatitudeRetrieval(): void
    {
        $this->assertEquals($this->point->getLatitude(), $this->lat);
    }

    public function testLongitudeRetrieval(): void
    {
        $this->assertEquals($this->lon, $this->point->getLongitude());
    }

    public function testToStringMethod(): void
    {
        $this->assertEquals($this->lon.' '.$this->lat, (string) $this->point);
    }

    public function testToWktConversion(): void
    {
        $this->assertEquals('POINT('.$this->lon.' '.$this->lat.')', $this->point->toWkt());
    }

    public function testRelativePoint(): void
    {
        $newPoint = $this->point->getRelativePoint(2.783, 98.50833, 'km');
        $this->assertEquals(53.48204, round($newPoint->getLatitude(), 5));
        $this->assertEquals(-2.23194, round($newPoint->getLongitude(), 5));
    }

    public function testDistanceTo(): void
    {
        $newPoint = new Point(-2.23194, 53.48204);
        $this->assertEquals(1.729, round($this->point->distanceTo($newPoint, UnitConverter::UNIT_MILES), 3));
        $this->assertEquals(2.783, round($this->point->distanceTo($newPoint, UnitConverter::UNIT_KM), 3));
        $this->assertEquals(
            2.792,
            round($this->point->distanceTo($newPoint, UnitConverter::UNIT_KM, new VincentyCalculator()), 3)
        );
    }

    public function testDistanceToException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $newPoint = new Point(-2.23194, 53.48204);
        $this->point->distanceTo($newPoint, 'foo');
    }

    public function testJsonSerializable(): void
    {
        $geoJSON = json_encode($this->point);
        $this->assertIsString($geoJSON);
        $this->assertJsonStringEqualsJsonString('{"type":"Point", "coordinates":[-2.27354, 53.48575]}', $geoJSON);
    }

    public function testFromDms(): void
    {
        $point = Point::fromDms(
            new DegreesMinutesSeconds(1, 2, 3.45, 'N'),
            new DegreesMinutesSeconds(0, 6, 9, 'W')
        );

        $this->assertSame(1.0342916666667, $point->getLatitude());
        $this->assertSame(-0.1025, $point->getLongitude());
    }

    public function testFromDmsInverted(): void
    {
        $point = Point::fromDms(
            new DegreesMinutesSeconds(0, 6, 9, 'W'),
            new DegreesMinutesSeconds(1, 2, 3.45, 'N')
        );

        $this->assertSame(1.0342916666667, $point->getLatitude());
        $this->assertSame(-0.1025, $point->getLongitude());
    }

    public function testFractionAlongLine(): void
    {
        DefaultDistanceCalculator::disableGeoSpatialExtension();
        $this->fractionAlongLine();
        DefaultDistanceCalculator::enableGeoSpatialExtension();
        $this->fractionAlongLine();
    }

    public function testEqualsIsTrue(): void
    {
        $point1 = new Point(1.1, -1.3);
        $point2 = new Point(1.1, -1.3);
        $this->assertTrue($point1->equals($point2));
    }

    public function testEqualsIsFalse(): void
    {
        $point1 = new Point(1.1, -1.3);
        $this->assertFalse($point1->equals(new Point(1.1, 1.3)));
        $this->assertFalse($point1->equals(new Point(1.1, -1.31)));
        $this->assertFalse($point1->equals(new LineString([new Point(1.1, -1.3), new Point(1.1, -1.31)])));
    }

    public function geoHahProvider(): Generator
    {
        yield ['u4pruydqqvj', 10.40744, 57.64911];
    }

    /**
     * @dataProvider geoHahProvider
     */
    public function testGetGeoHash(string $hash, float $lon, float $lat): void
    {
        $point = new Point($lon, $lat);
        $this->assertSame($hash, (string) $point->getGeoHash(11));
    }

    private function fractionAlongLine(): void
    {
        $point1 = Point::fromArray([5, 10]);
        $point2 = Point::fromArray([15, 10]);

        $fraction02 = $point1->getFractionAlongLineTo($point2, 0.2);
        $fraction05 = $point1->getFractionAlongLineTo($point2, 0.5);
        $midpoint = $point1->getMidpoint($point2);

        $this->assertEquals(6.9998522347268, $fraction02->getLongitude());
        $this->assertEquals(10.023944943799, $fraction02->getLatitude());
        $this->assertEquals($midpoint->getLatitude(), $fraction05->getLatitude());
        $this->assertEquals($midpoint->getLongitude(), $fraction05->getLongitude());
    }
}
