<?php

namespace Ricklab\Location;


class LineStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LineString
     */
    protected $line;

    public function setUp()
    {
        $point1     = new Point( 53.48575, - 2.27354 );
        $point2     = new Point( 53.48204, - 2.23194 );
        $this->line = new LineString( $point1, $point2 );
    }

    public function testConstructor()
    {
        $point1 = new Point( 53.48575, - 2.27354 );
        $point2 = new Point( 53.48204, - 2.23194 );
        $line   = new LineString( $point1, $point2 );

        $line2 = new LineString( [ $point1, $point2 ] );

        $this->assertInstanceOf( '\Ricklab\Location\LineString', $line );
        $this->assertInstanceOf( '\Ricklab\Location\LineString', $line2 );

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOnePointException()
    {
        $point1 = new Point( 53.48575, - 2.27354 );

        $line = new LineString( $point1 );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidPointException()
    {
        $point1 = new Point( 53.48575, - 2.27354 );

        $line = new LineString( [ $point1, 'foo' ] );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOnePointInArrayException()
    {
        $point1 = new Point( 53.48575, - 2.27354 );

        $line = new LineString( [ $point1 ] );
    }

    public function testGetLength()
    {
        $this->assertEquals( 2.783, round( $this->line->getLength(), 3 ) );
        $this->assertEquals( 2.792, round( $this->line->getLength( 'km', Location::VINCENTY ), 3 ) );
    }

    public function testInitialBearing()
    {
        $this->assertEquals( 98.50702, round( $this->line->getInitialBearing(), 5 ) );
    }

    public function testGeoJson()
    {
        $retval = json_encode( $this->line );
        $this->assertEquals( '{"type":"LineString","coordinates":[[-2.27354,53.48575],[-2.23194,53.48204]]}', $retval );
    }

    public function testToArray()
    {
        $retval = [ [ - 2.27354, 53.48575 ], [ - 2.23194, 53.48204 ] ];

        $this->assertEquals( $retval, $this->line->toArray() );
    }

    public function testToString()
    {
        $retval = '(-2.27354 53.48575, -2.23194 53.48204)';
        $this->assertEquals( $retval, (string) $this->line );
    }

    public function testBBox()
    {
        $this->assertEquals(
            '{"type":"Polygon","coordinates":[[[-2.27354,53.48575],[-2.23194,53.48575],[-2.23194,53.48204],[-2.27354,53.48204],[-2.27354,53.48575]]]}',
            json_encode( $this->line->getBBox() ) );
    }

}
