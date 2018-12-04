<?php

declare(strict_types=1);

namespace Ricklab\Location;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Geometry\Point;

class LocationTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testCreatePoint()
    {
        $point = Location::fromGeoJson('{"type":"Point","coordinates":[-2.27354,53.48575]}');

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals(-2.27354, $point->getLongitude());
        $this->assertEquals(53.48575, $point->getLatitude());
    }

    public function testCreateLineString()
    {
        $line = Location::fromGeoJson(
            '{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204]]}'
        );

        $this->assertInstanceOf('Ricklab\Location\Geometry\LineString', $line);
        $this->assertEquals(2.783, \round($line->getLength(), 3));

        $multiPointLine = Location::fromGeoJson(
            '{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204], [-2.23144,53.48254]]}'
        );
        $this->assertInstanceOf('Ricklab\Location\Geometry\LineString', $multiPointLine);
    }

    public function testCreatePolygon()
    {
        $polygon = Location::fromGeoJson('{"type":"Polygon","coordinates":[[[3,2],[4,2],[4,3],[3,2]]]}');

        $this->assertInstanceOf('Ricklab\Location\Geometry\Polygon', $polygon);

        $retVal = $polygon->toWkt();
        $this->assertEquals('POLYGON((3 2, 4 2, 4 3, 3 2))', $retVal);
    }

    public function testConvert()
    {
        $this->assertEquals(5.754, \round(Location::convert(5, 'nm', 'miles'), 3));

        $this->assertEquals(300, \round(Location::convert(100, 'yards', 'ft'), 3));

        $this->assertEquals(8.047, \round(Location::convert(5, 'miles', 'km'), 3));
    }

    public function testVincenty()
    {
        $flinders = new Geometry\Point(-37.95103341666667, 144.42486788888888);

        $buninyond = new Geometry\Point(-37.65282113888889, 143.92649552777777);

        Location::$useSpatialExtension = true;
        $this->assertEquals(54972.271, Location::vincenty($flinders, $buninyond));

        Location::$useSpatialExtension = false;
        $this->assertEquals(54972.271, Location::vincenty($flinders, $buninyond));
        Location::$useSpatialExtension = true;
    }

    public function testDmsToDecimal()
    {
        $decimal = Location::dmsToDecimal(117, 29, 50.5);

        $this->assertEquals(117.49736, \round($decimal, 5));

        $decimal2 = Location::dmsToDecimal(1, 2, 3.45, 'W');

        $this->assertEquals(-1.0342916666667, $decimal2);
    }

    public function testDecimalToDms()
    {
        $dms = Location::decimalToDms(1.0342916666667);
        $dms[2] = \round($dms[2], 5);
        $this->assertEquals([1, 2, 3.45], $dms);
    }

    public function testFromWkt()
    {
        $multipolywkt = 'MULTIPOLYGON(((1.432 -1.543, 5 1, 5 5, 1 5, 1.432 -1.543),(2 2, 3 2, 3 3, 2 3, 2 2)),((3 3, 6 2, 6 4, 3 3)))';
        $multilinewkt = 'MULTILINESTRING((3 4, 10 50, 20 25),(-5 -8, -10 -8, -15 -4))';
        $pointwkt = 'POINT(4 5)';
        $multipoly = Location::fromWkt($multipolywkt);
        $multiline = Location::fromWkt($multilinewkt);
        $point = Location::fromWkt($pointwkt);

        $this->assertInstanceOf(Geometry\Point::class, $point);
        $this->assertInstanceOf(Geometry\MultiPolygon::class, $multipoly);
        $this->assertInstanceOf(Geometry\MultiLineString::class, $multiline);
        $this->assertEquals([4, 5], $point->toArray());
        $this->assertEquals($multipolywkt, $multipoly->toWkt());
        $this->assertEquals($multilinewkt, $multiline->toWkt());
    }
}
