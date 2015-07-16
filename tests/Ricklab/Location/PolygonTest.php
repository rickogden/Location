<?php

namespace Ricklab\Location\Tests;

require __DIR__ . '/../../../vendor/autoload.php';

use Ricklab\Location;

class PolygonTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \Ricklab\Location\Polygon
     */
    public $polygon;

    public function setUp()
    {

        Location\Location::$useSpatialExtension = false;
        $this->polygon = new Location\Polygon([new Location\Point(2, 3), new Location\Point(2, 4), new Location\Point(3, 4)]);
    }

    public function testConstruction()
    {
        $poly1 = new Location\Polygon( [
            [
                new Location\Point( 2, 3 ),
                new Location\Point( 2, 4 ),
                new Location\Point( 3, 4 ),
                new Location\Point( 2, 3 )
            ]
        ] );
        $this->assertEquals( $this->polygon, $poly1 );

        $poly2 = new Location\Polygon( [
            new Location\LineString( [
                new Location\Point( 2, 3 ),
                new Location\Point( 2, 4 ),
                new Location\Point( 3, 4 ),
                new Location\Point( 2, 3 )
            ] )
        ] );
        $this->assertEquals( $this->polygon, $poly2 );
    }

    public function testLastPointIsTheSameAsFirstPoint()
    {
        $a = $this->polygon;
        $this->assertEquals( $a[0][0]->getLatitude(), $a[0][count( $a ) - 1]->getLatitude() );
        $this->assertEquals( $a[0][0]->getLongitude(), $a[0][count( $a ) - 1]->getLongitude() );
    }

    public function testToArrayReturnsAnArray()
    {
        $this->assertTrue(is_array($this->polygon->toArray()));
    }

    public function testObjectIsAPolygon()
    {

        $this->assertInstanceOf('Ricklab\Location\Polygon', $this->polygon);
    }

    public function testToString()
    {
        $retval = '((3 2, 4 2, 4 3, 3 2))';
        $this->assertEquals( $retval, (string) $this->polygon );
    }

    public function testToWkt()
    {
        $retVal = $this->polygon->toWkt();
        $this->assertEquals( 'POLYGON((3 2, 4 2, 4 3, 3 2))', $retVal );
    }

    public function tearDown()
    {
        $this->polygon = null;
    }

    public function testJsonSerialize()
    {
        $json = json_encode($this->polygon);
        $this->assertEquals('{"type":"Polygon","coordinates":[[[3,2],[4,2],[4,3],[3,2]]]}', $json);
    }

    public function testBBox()
    {
        $polygon = new Location\Polygon( [
            [
                new Location\Point( 3, 4 ),
                new Location\Point( 2, 3 ),
                new Location\Point( 2, 4 ),
                new Location\Point( 3, 2 )
            ]
        ] );
        $this->assertEquals( '{"type":"Polygon","coordinates":[[[2,3],[4,3],[4,2],[2,2],[2,3]]]}',
            json_encode( $polygon->getBBox() ) );

        $this->assertEquals( '{"type":"Polygon","coordinates":[[[3,3],[4,3],[4,2],[3,2],[3,3]]]}',
            json_encode( $this->polygon->getBBox() ) );
    }

}
