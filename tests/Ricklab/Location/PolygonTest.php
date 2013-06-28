<?php

namespace Ricklab\Location\Tests;

require __DIR__ . '/../../../vendor/autoload.php';

use \Ricklab\Location;

class PolygonTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Ricklab\Location\Polygon 
     */
    public $polygon;

    public function setUp()
    {
        $this->polygon = new Location\Polygon([new Location\Point(2, 3), new Location\Point(2, 4), new Location\Point(3, 4)]);
    }

    public function testLastPointIsTheSameAsFirstPoint()
    {
        $a = $this->polygon->toArray();
        $this->assertEquals($a[0]->getLatitude(), $a[count($a) - 1]->getLatitude());
        $this->assertEquals($a[0]->getLongitude(), $a[count($a) - 1]->getLongitude());
    }

    public function testToArrayReturnsAnArray()
    {
        $this->assertTrue(is_array($this->polygon->toArray()));
    }

    public function testObjectIsAPolygon()
    {

        $this->assertInstanceOf('Ricklab\Location\Polygon', $this->polygon);
    }

    public function tearDown()
    {
        $this->polygon = null;
    }

}
