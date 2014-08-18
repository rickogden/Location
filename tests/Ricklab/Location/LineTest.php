<?php

namespace Ricklab\Location\Tests;

require __DIR__.'/../../../vendor/autoload.php';

use \Ricklab\Location;

class LineTest extends \PHPUnit_Framework_TestCase {
    
    /**
     *
     * @var \Ricklab\Location\Line 
     */
    public $line;
    
    /**
     *
     * @var \Ricklab\Location\Point
     */
    public $start, $end;
    
    public function setUp() {
        $this->start = new Location\Point(53.48575, -2.27354);
        $this->end = new Location\Point(53.48204, -2.23194);
        $this->line = new Location\Line($this->start, $this->end);
    }
    
    public function testLineIsInstanceOfLineClass() {
        $this->assertTrue($this->line instanceof Location\Line);
    }
    
    public function testBearing() {
        $this->assertEquals(round($this->line->getBearing(), 5), 98.50702);
    }
    
    public function testLength() {
        $this->assertEquals(round($this->line->getLength(), 3), 2.783);
    }
    
    public function testMidPoint() {
        $midPoint = $this->line->getMidPoint();
        $this->assertTrue($midPoint instanceof Location\Point);
        $this->assertEquals(53.4839,round($midPoint->lat, 5));
        $this->assertEquals(-2.25274, round($midPoint->lon, 5));
                
    }

    public function testToSql() {
        $retVal = $this->line->toSql();
        $this->assertEquals('LineString(53.48575 -2.27354, 53.48204 -2.23194)', $retVal);
    }

    public function testGeoJson()
    {
        $retval = json_encode($this->line);
        $this->assertEquals('{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204]]}', $retval);
    }
}

