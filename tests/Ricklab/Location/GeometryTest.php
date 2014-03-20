<?php

namespace Ricklab\Location\Tests;

require __DIR__ . '/../../../vendor/autoload.php';

use \Ricklab\Location;

class GeometryTest extends \PHPUnit_Framework_TestCase
{


    public function testCreatePoint()
    {
        $point = Location\Geometry::fromGeoJson('{"type":"Point","coordinates":[-2.27354,53.48575]}');

        $this->assertInstanceOf('Ricklab\Location\Point', $point);
        $this->assertEquals(-2.27354, $point->lon);
        $this->assertEquals(53.48575, $point->lat);

    }

    public function testCreateLineString()
    {

        $line = Location\Geometry::fromGeoJson(
            '{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204]]}'
        );

        $this->assertInstanceOf('Ricklab\Location\Line', $line);
        $this->assertEquals(round($line->getLength()->toKm(), 3), 2.783);

        $multiPointLine = Location\Geometry::fromGeoJson(
            '{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204], [-2.23144,53.48254]]}'
        );
        $this->assertInstanceOf('Ricklab\Location\MultiPointLine', $multiPointLine);
    }

    public function testCreatePolygon()
    {

        $polygon = Location\Geometry::fromGeoJson('{"type":"Polygon","coordinates":[[[3,2],[4,2],[4,3],[3,2]]]}');

        $this->assertInstanceOf('Ricklab\Location\Polygon', $polygon);

        $retVal = $polygon->toSql();
        $this->assertEquals('POLYGON((2 3, 2 4, 3 4, 2 3))', $retVal);


    }


}