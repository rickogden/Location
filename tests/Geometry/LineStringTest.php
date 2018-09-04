<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Location;

class LineStringTest extends TestCase
{
    /**
     * @var LineString
     */
    protected $line;

    protected function setUp()
    {
        $point1 = new Point(53.48575, -2.27354);
        $point2 = new Point(53.48204, -2.23194);
        $this->line = new LineString($point1, $point2);
    }

    public function testConstructor()
    {
        $point1 = new Point(53.48575, -2.27354);
        $point2 = new Point(53.48204, -2.23194);
        $line = new LineString($point1, $point2);

        $line2 = new LineString([$point1, $point2]);

        $this->assertInstanceOf('\Ricklab\Location\Geometry\LineString', $line);
        $this->assertInstanceOf('\Ricklab\Location\Geometry\LineString', $line2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOnePointException()
    {
        $point1 = new Point(53.48575, -2.27354);

        $line = new LineString($point1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidPointException()
    {
        $point1 = new Point(53.48575, -2.27354);

        $line = new LineString([$point1, 'foo']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOnePointInArrayException()
    {
        $point1 = new Point(53.48575, -2.27354);

        $line = new LineString([$point1]);
    }

    public function testGetLength()
    {
        $this->assertEquals(2.783, \round($this->line->getLength(), 3));
        $this->assertEquals(2.792, \round($this->line->getLength('km', Location::VINCENTY), 3));
    }

    public function testInitialBearing()
    {
        Location::$useSpatialExtension = false;
        $this->assertEquals(98.50702, \round($this->line->getInitialBearing(), 5));
        Location::$useSpatialExtension = true;
        $this->assertEquals(98.50702, \round($this->line->getInitialBearing(), 5));
    }

    public function testGeoJson()
    {
        $retval = \json_encode($this->line);
        $this->assertEquals('{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204]]}', $retval);
    }

    public function testToArray()
    {
        $retval = [[-2.27354, 53.48575], [-2.23194, 53.48204]];

        $this->assertEquals($retval, $this->line->toArray());
    }

    public function testToString()
    {
        $retval = '(-2.27354 53.48575, -2.23194 53.48204)';
        $this->assertEquals($retval, (string) $this->line);
    }

    public function testBBox()
    {
        $this->assertEquals(
            '{"type":"Polygon","coordinates":[[[-2.27354,53.48575],[-2.23194,53.48575],[-2.23194,53.48204],[-2.27354,53.48204],[-2.27354,53.48575]]]}',
            \json_encode($this->line->getBBox())
        );
    }

    public function testFromArray()
    {
        $line = new LineString([[-2.27354, 53.48575], [-2.23194, 53.48204]]);

        $point1 = $line[0];
        $point2 = $line[1];
        $this->assertInstanceOf('\Ricklab\Location\Geometry\Point', $point1);
        $this->assertInstanceOf('\Ricklab\Location\Geometry\Point', $point2);

        $this->assertEquals(-2.27354, $point1->getLongitude());
        $this->assertEquals(53.48204, $point2->getLatitude());
    }

    public function testReverse()
    {
        $original = [[-2.27354, 53.48575], [-2.23194, 53.48204]];

        $lineString = new LineString($original);
        $lineString->reverse();
        $this->assertEquals($original[0], $lineString->toArray()[1]);
        $this->assertEquals($original[1], $lineString->toArray()[0]);
    }
}
