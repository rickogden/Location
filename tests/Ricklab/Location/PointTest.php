<?php

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Location;

require __DIR__ . '/../../../vendor/autoload.php';

class PointTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \Ricklab\Location\Geometry\Point
     */
    public $point;
    public $lat = 53.48575;
    public $lon = -2.27354;

    public function setUp()
    {

        Location::$useSpatialExtension = false;
        $this->point                   = new Point( $this->lat, $this->lon );
    }

    public function testInstanceOfClassIsAPoint()
    {
        $this->assertTrue( $this->point instanceof Point );
    }

    public function testPointCreationAsArray()
    {
        $point = new Point( [ $this->lon, $this->lat ] );
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
        $this->assertEquals( $this->lon . ' ' . $this->lat, (string) $this->point );
    }

    public function testToWktConversion()
    {
        $this->assertEquals( 'POINT(' . $this->lon . ' ' . $this->lat . ')', $this->point->toWkt() );
    }

    public function testRelativePoint()
    {
        $newPoint = $this->point->getRelativePoint(2.783, 98.50833, 'km');
        $this->assertEquals( 53.48204, round( $newPoint->lat, 5 ) );
        $this->assertEquals( - 2.23194, round( $newPoint->lon, 5 ) );
    }

    public function testDistanceTo()
    {
        $newPoint = new Point( 53.48204, - 2.23194 );
        $this->assertEquals( 1.729, round( $this->point->distanceTo( $newPoint, 'miles' ), 3 ) );
        $this->assertEquals( 2.783, round( $this->point->distanceTo( $newPoint ), 3 ) );
        $this->assertEquals( 2.792,
            round( $this->point->distanceTo( $newPoint, 'km', Location::VINCENTY ), 3 ) );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDistanceToException()
    {
        $newPoint = new Point( 53.48204, - 2.23194 );
        $this->point->distanceTo( $newPoint, 'foo' );
    }

    public function testJsonSerializable()
    {
        $geoJSON = json_encode($this->point);
        $this->assertInternalType('string', $geoJSON);
        $this->assertJsonStringEqualsJsonString('{"type":"Point", "coordinates":[-2.27354, 53.48575]}', $geoJSON);
    }

    public function testFromDms()
    {
        $point = Point::fromDms( [ 1, 2, 3.45 ], [ 0, 6, 9, 'S' ] );

        $this->assertEquals( 1.0342916666667, $point->getLatitude() );

        $this->assertEquals( - 0.1025, $point->getLongitude() );


    }


    public function testFractionAlongLine()
    {

        $point1 = new Point([5, 10]);
        $point2 = new Point([15, 10]);

        $fraction02 = $point1->getFractionAlongLineTo($point2, 0.2);
        $fraction05 = $point1->getFractionAlongLineTo($point2, 0.5);
        $midpoint   = $point1->getMidpoint($point2);

        $this->assertEquals(6.9998522347268, $fraction02->getLongitude());
        $this->assertEquals(10.023944943799, $fraction02->getLatitude());
        $this->assertEquals($midpoint->getLatitude(), $fraction05->getLatitude());
        $this->assertEquals($midpoint->getLongitude(), $fraction05->getLongitude());
    }

}
