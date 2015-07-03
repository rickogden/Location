<?php

namespace Ricklab\Location\Tests;

require __DIR__ . '/../../../vendor/autoload.php';

use \Ricklab\Location;

class LocationTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {

        Location\Location::$useSpatialExtension = false;
    }


    public function testCreatePoint()
    {
        $point = Location\Location::fromGeoJson('{"type":"Point","coordinates":[-2.27354,53.48575]}');

        $this->assertInstanceOf('Ricklab\Location\Point', $point);
        $this->assertEquals(-2.27354, $point->lon);
        $this->assertEquals(53.48575, $point->lat);

    }

    public function testCreateLineString()
    {

        $line = Location\Location::fromGeoJson(
            '{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204]]}'
        );

        $this->assertInstanceOf('Ricklab\Location\Line', $line);
        $this->assertEquals(round($line->getLength(), 3), 2.783);

        $multiPointLine = Location\Location::fromGeoJson(
            '{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204], [-2.23144,53.48254]]}'
        );
        $this->assertInstanceOf('Ricklab\Location\MultiPointLine', $multiPointLine);
    }

    public function testCreatePolygon()
    {

        $polygon = Location\Location::fromGeoJson('{"type":"Polygon","coordinates":[[[3,2],[4,2],[4,3],[3,2]]]}');

        $this->assertInstanceOf('Ricklab\Location\Polygon', $polygon);

        $retVal = $polygon->toSql();
        $this->assertEquals('POLYGON((2 3, 2 4, 3 4, 2 3))', $retVal);


    }

    public function testConvert()
    {
        $this->assertEquals( 5.754, round( Location\Location::convert( 5, 'nm', 'miles' ), 3 ) );

        $this->assertEquals( 300, round( Location\Location::convert( 100, 'yards', 'ft' ), 3 ) );

        $this->assertEquals( 8.047, round( Location\Location::convert( 5, 'miles', 'km' ), 3 ) );


    }

    public function testVincenty()
    {
        $flinders = new Location\Point( - 37.95103341666667, 144.42486788888888 );

        $buninyond = new Location\Point( - 37.65282113888889, 143.92649552777777 );

        $this->assertEquals( 54972.2, Location\Location::vincenty( $flinders, $buninyond ) );
    }



}