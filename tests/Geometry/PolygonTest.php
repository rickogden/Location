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

    protected function setUp(): void
    {
        Location::$useSpatialExtension = false;
        $this->polygon = Polygon::fromArray([[new Point(3, 2), new Point(4, 2), new Point(4, 3)]]);
    }

    public function testConstruction(): void
    {
        $poly1 = new Polygon([
            new LineString([
                new Point(3, 2),
                new Point(4, 2),
                new Point(4, 3),
                new Point(3, 2),
            ]),
        ]);
        $this->assertEquals($this->polygon->toArray(), $poly1->toArray());

        $poly2 = Polygon::fromArray([[[3, 2], [4, 2], [4, 3], [3, 2]]]);
        $this->assertEquals($this->polygon->toArray(), $poly2->toArray());
    }

    public function testGetPoints(): void
    {
        $poly1 = new Polygon([
            new LineString([
                new Point(3, 2),
                new Point(4, 2),
                new Point(4, 3),
                new Point(3, 2),
            ]),
            LineString::fromArray([[1, 2], [2, 3]]),
        ]);

        $points = $poly1->getPoints();
        $this->assertCount(7, $points);
    }

    public function testLastPointIsTheSameAsFirstPoint(): void
    {
        $a = $this->polygon;
        $this->assertEquals($a->toArray()[0][0][0], $a->toArray()[0][\count($a->toArray()[0]) - 1][0]);
        $this->assertEquals($a->toArray()[0][0][1], $a->toArray()[0][\count($a->toArray()[0]) - 1][1]);
    }

    public function testToString(): void
    {
        $retval = '((3 2, 4 2, 4 3, 3 2))';
        $this->assertEquals($retval, (string) $this->polygon);
    }

    public function testToWkt(): void
    {
        $retVal = $this->polygon->toWkt();
        $this->assertEquals('POLYGON((3 2, 4 2, 4 3, 3 2))', $retVal);
    }

    protected function tearDown()
    {
        $this->polygon = null;
    }

    public function testJsonSerialize(): void
    {
        $json = \json_encode($this->polygon);
        $this->assertEquals('{"type":"Polygon","coordinates":[[[3,2],[4,2],[4,3],[3,2]]]}', $json);
    }

    public function testBBox(): void
    {
        $polygon = new Polygon([
            new LineString([
                new Point(4, 3),
                new Point(3, 2),
                new Point(4, 2),
                new Point(2, 3),
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

    public function testFromArray(): void
    {
        $ar = [[[100.0, 0.0], [101.0, 1.0], [102.0, 2.0], [103.0, 3.0]]];

        $polygon = Polygon::fromArray($ar);

        $this->assertEquals([100.0, 0.0], $polygon->getLineStrings()[0]->getPoints()[0]->toArray());
    }
}
