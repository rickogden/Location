<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Location;

class PolygonTest extends TestCase
{
    /**
     * @var \Ricklab\Location\Geometry\Polygon
     */
    public $polygon;

    protected function setUp()
    {
        Location::$useSpatialExtension = false;
        $this->polygon = Polygon::fromArray([[new Point(2, 3), new Point(2, 4), new Point(3, 4)]]);
    }

    public function testConstruction()
    {
        $poly1 = new Polygon([
            new LineString([
                new Point(2, 3),
                new Point(2, 4),
                new Point(3, 4),
                new Point(2, 3),
            ]),
        ]);
        $this->assertEquals($this->polygon->toArray(), $poly1->toArray());

        $poly2 = Polygon::fromArray([[[3, 2], [4, 2], [4, 3], [3, 2]]]);
        $this->assertEquals($this->polygon->toArray(), $poly2->toArray());
    }

    public function testLastPointIsTheSameAsFirstPoint()
    {
        $a = $this->polygon;
        $this->assertEquals($a->toArray()[0][0][0], $a->toArray()[0][\count($a->toArray()[0]) - 1][0]);
        $this->assertEquals($a->toArray()[0][0][1], $a->toArray()[0][\count($a->toArray()[0]) - 1][1]);
    }

    public function testToString()
    {
        $retval = '((3 2, 4 2, 4 3, 3 2))';
        $this->assertEquals($retval, (string) $this->polygon);
    }

    public function testToWkt()
    {
        $retVal = $this->polygon->toWkt();
        $this->assertEquals('POLYGON((3 2, 4 2, 4 3, 3 2))', $retVal);
    }

    protected function tearDown()
    {
        $this->polygon = null;
    }

    public function testJsonSerialize()
    {
        $json = \json_encode($this->polygon);
        $this->assertEquals('{"type":"Polygon","coordinates":[[[3,2],[4,2],[4,3],[3,2]]]}', $json);
    }

    public function testBBox()
    {
        $polygon = new Polygon([
            new LineString([
                new Point(3, 4),
                new Point(2, 3),
                new Point(2, 4),
                new Point(3, 2),
            ]),
        ]);
        $this->assertEquals(
            '{"type":"Polygon","coordinates":[[[2,3],[4,3],[4,2],[2,2],[2,3]]]}',
            \json_encode($polygon->getBBox())
        );

        $this->assertEquals(
            '{"type":"Polygon","coordinates":[[[3,3],[4,3],[4,2],[3,2],[3,3]]]}',
            \json_encode($this->polygon->getBBox())
        );
    }

    public function testFromArray()
    {
        $ar = [[[100.0, 0.0], [101.0, 1.0], [102.0, 2.0], [103.0, 3.0]]];

        $polygon = Polygon::fromArray($ar);

        $this->assertEquals([100.0, 0.0], $polygon->getLineStrings()[0]->getPoints()[0]->toArray());
    }
}
