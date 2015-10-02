<?php

namespace Ricklab\Location;

require __DIR__ . '/../../../vendor/autoload.php';

use Ricklab\Location\Geometry;

class LocationTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {

        Location::$useSpatialExtension = false;
    }


    public function testCreatePoint()
    {
        $point = Location::fromGeoJson( '{"type":"Point","coordinates":[-2.27354,53.48575]}' );

        $this->assertInstanceOf( 'Ricklab\Location\Geometry\Point', $point );
        $this->assertEquals(-2.27354, $point->lon);
        $this->assertEquals(53.48575, $point->lat);

    }

    public function testCreateLineString()
    {

        $line = Location::fromGeoJson(
            '{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204]]}'
        );

        $this->assertInstanceOf( 'Ricklab\Location\Geometry\LineString', $line );
        $this->assertEquals( 2.783, round( $line->getLength(), 3 ) );

        $multiPointLine = Location::fromGeoJson(
            '{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204], [-2.23144,53.48254]]}'
        );
        $this->assertInstanceOf( 'Ricklab\Location\Geometry\LineString', $multiPointLine );
    }

    public function testCreatePolygon()
    {

        $polygon = Location::fromGeoJson( '{"type":"Polygon","coordinates":[[[3,2],[4,2],[4,3],[3,2]]]}' );

        $this->assertInstanceOf( 'Ricklab\Location\Geometry\Polygon', $polygon );

        $retVal = $polygon->toWkt();
        $this->assertEquals( 'POLYGON((3 2, 4 2, 4 3, 3 2))', $retVal );


    }

    public function testConvert()
    {
        $this->assertEquals( 5.754, round( Location::convert( 5, 'nm', 'miles' ), 3 ) );

        $this->assertEquals( 300, round( Location::convert( 100, 'yards', 'ft' ), 3 ) );

        $this->assertEquals( 8.047, round( Location::convert( 5, 'miles', 'km' ), 3 ) );


    }

    public function testVincenty()
    {
        $flinders = new Geometry\Point( - 37.95103341666667, 144.42486788888888 );

        $buninyond = new Geometry\Point( - 37.65282113888889, 143.92649552777777 );

        $this->assertEquals( 54972.271, Location::vincenty( $flinders, $buninyond ) );
    }


    public function testDmsToDecimal()
    {
        $decimal = Location::dmsToDecimal( 117, 29, 50.5 );

        $this->assertEquals( 117.49736, round( $decimal, 5 ) );

        $decimal2 = Location::dmsToDecimal( 1, 2, 3.45, 'W' );

        $this->assertEquals( - 1.0342916666667, $decimal2 );
    }

    public function testDecimalToDms()
    {
        $dms    = Location::decimalToDms( 1.0342916666667 );
        $dms[2] = round( $dms[2], 5 );
        $this->assertEquals( [ 1, 2, 3.45 ], $dms );
    }

}