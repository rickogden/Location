<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Location;

class PointTest extends TestCase
{
    /**
     * @var \Ricklab\Location\Geometry\Point
     */
    public $point;
    public $lat = 53.48575;
    public $lon = -2.27354;

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
        $this->assertEquals(53.48204, \round($newPoint->getLatitude(), 5));
        $this->assertEquals(-2.23194, \round($newPoint->getLongitude(), 5));
    }

    public function testDistanceTo(): void
    {
        $newPoint = new Point(-2.23194, 53.48204);
        $this->assertEquals(1.729, \round($this->point->distanceTo($newPoint, 'miles'), 3));
        $this->assertEquals(2.783, \round($this->point->distanceTo($newPoint), 3));
        $this->assertEquals(
            2.792,
            \round($this->point->distanceTo($newPoint, 'km', Location::FORMULA_VINCENTY), 3)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDistanceToException(): void
    {
        $newPoint = new Point(-2.23194, 53.48204);
        $this->point->distanceTo($newPoint, 'foo');
    }

    public function testJsonSerializable(): void
    {
        $geoJSON = \json_encode($this->point);
        $this->assertInternalType('string', $geoJSON);
        $this->assertJsonStringEqualsJsonString('{"type":"Point", "coordinates":[-2.27354, 53.48575]}', $geoJSON);
    }

    public function testFromDms(): void
    {
        $point = Point::fromDms([1, 2, 3.45], [0, 6, 9, 'S']);

        $this->assertEquals(1.0342916666667, $point->getLatitude());

        $this->assertEquals(-0.1025, $point->getLongitude());
    }

    public function testFractionAlongLine(): void
    {
        Location::$useSpatialExtension = false;
        $this->fractionAlongLine();
        Location::$useSpatialExtension = true;
        $this->fractionAlongLine();
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
