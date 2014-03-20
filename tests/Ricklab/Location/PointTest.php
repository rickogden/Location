<?php

namespace Ricklab\Location\Tests;

require __DIR__ . '/../../../vendor/autoload.php';

use \Ricklab\Location;

class PointTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \Ricklab\Location\Point
     */
    public $point;
    public $lat = 53.48575;
    public $lon = -2.27354;

    public function setUp()
    {
        $this->point = new Location\Point($this->lat, $this->lon);
    }

    public function testInstanceOfClassIsAPoint()
    {
        $this->assertTrue($this->point instanceof Location\Point);
    }

    public function testPointCreationAsArray()
    {
        $point = new Location\Point([$this->lon, $this->lat]);
        $this->assertEquals($this->lat, $point->getLatitude());
        $this->assertEquals($this->lon, $point->getLongitude());
    }

    public function testLatitudeRetrieval()
    {
        $this->assertEquals($this->point->getLatitude(), $this->lat);
        $this->assertEquals($this->point->lat, $this->lat);
        $this->assertEquals($this->point->latitude, $this->lat);
    }

    public function testLongitudeRetrieval()
    {
        $this->assertEquals($this->point->getLongitude(), $this->lon);
        $this->assertEquals($this->point->lon, $this->lon);
        $this->assertEquals($this->point->longitude, $this->lon);
    }

    public function testToStringMethod()
    {
        $this->assertEquals((string) $this->point, $this->lat . ' ' . $this->lon);
    }

    public function testToSqlConversion()
    {
        $this->assertEquals($this->point->toSql(), 'POINT(' . $this->lat . ' ' . $this->lon . ')');
    }

    public function testRelativePoint()
    {
        $newPoint = $this->point->getRelativePoint(2.783, 98.50833, 'km');
        $this->assertEquals(round($newPoint->lat, 5), 53.48204);
        $this->assertEquals(round($newPoint->lon, 5), -2.23194);
    }

    public function testDistanceTo()
    {
        $newPoint = new Location\Point(53.48204, -2.23194);
        $this->assertEquals(round($this->point->distanceTo($newPoint)->toKm(), 3), 2.783);
    }

    public function testJsonSerializable()
    {
        $geoJSON = json_encode($this->point);
        $this->assertInternalType('string', $geoJSON);
        $this->assertJsonStringEqualsJsonString('{"type":"Point", "coordinates":[-2.27354, 53.48575]}', $geoJSON);
    }

}
