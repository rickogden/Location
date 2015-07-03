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

        Location\Location::$useSpatialExtension = false;
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
        $this->assertEquals( $this->lat . ' ' . $this->lon, (string) $this->point );
    }

    public function testToSqlConversion()
    {
        $this->assertEquals( 'POINT(' . $this->lat . ' ' . $this->lon . ')', $this->point->toSql() );
    }

    public function testRelativePoint()
    {
        $newPoint = $this->point->getRelativePoint(2.783, 98.50833, 'km');
        $this->assertEquals( 53.48204, round( $newPoint->lat, 5 ) );
        $this->assertEquals( - 2.23194, round( $newPoint->lon, 5 ) );
    }

    public function testDistanceTo()
    {
        $newPoint = new Location\Point(53.48204, -2.23194);
        $this->assertEquals( 1.729, round( $this->point->distanceTo( $newPoint, 'miles' ), 3 ) );
        $this->assertEquals( 2.783, round( $this->point->distanceTo( $newPoint ), 3 ) );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDistanceToException()
    {
        $newPoint = new Location\Point( 53.48204, - 2.23194 );
        $this->point->distanceTo( $newPoint, 'foo' );
    }

    public function testJsonSerializable()
    {
        $geoJSON = json_encode($this->point);
        $this->assertInternalType('string', $geoJSON);
        $this->assertJsonStringEqualsJsonString('{"type":"Point", "coordinates":[-2.27354, 53.48575]}', $geoJSON);
    }

}
