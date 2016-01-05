<?php
/**
 * Author: rick
 * Date: 02/12/2015
 * Time: 11:17
 */

namespace Ricklab\Location\Geometry;



class MultiLineStringTest extends \PHPUnit_Framework_TestCase
{


    public function testToGeoJson()
    {
        $geojson = '{ "type": "MultiLineString",
    "coordinates": [
        [ [100.0, 0.0], [101.0, 1.0] ],
        [ [102.0, 2.0], [103.0, 3.0] ]
      ]
    }';

        $geojson = json_encode(json_decode($geojson), true);

        $lineString1     = new LineString([new Point([100.0, 0.0]), new  Point([101.0, 1.0])]);
        $lineString2     = new LineString([new Point([102.0, 2.0]), new Point([103.0, 3.0])]);
        $multiLineString = new MultiLineString([$lineString1, $lineString2]);

        $this->assertEquals($geojson, json_encode($multiLineString));
    }

    public function testToWkt()
    {
        $wkt = 'MULTILINESTRING((3 4, 10 50, 20 25),(-5 -8, -10 -8, -15 -4))';

        $lineString1 = new LineString([
            new Point([3, 4]),
            new Point([10, 50]),
            new Point([20, 25])
        ]);

        $lineString2 = new LineString([
            new Point([- 5, - 8]),
            new Point([- 10, - 8]),
            new Point([- 15, - 4])
        ]);

        $multiLineString = new MultiLineString([$lineString1, $lineString2]);

        $this->assertEquals($wkt, $multiLineString->toWkt());
    }

}
