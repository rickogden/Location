<?php
/**
 * Author: rick
 * Date: 03/12/2015
 * Time: 11:10
 */

namespace Ricklab\Location\Geometry;


use Ricklab\Location\Location;

class GeometryCollectionTest extends \PHPUnit_Framework_TestCase
{

    public function testToGeoJson()
    {
        $json    = '{ "type": "GeometryCollection",
    "geometries": [
      { "type": "Point",
        "coordinates": [100.0, 0.0]
        },
      { "type": "LineString",
        "coordinates": [ [101.0, 0.0], [102.0, 1.0] ]
        }
    ]
  }';
        $geojson = json_encode(json_decode($json, true));

        $point      = new Point([100.0, 0.0]);
        $lineString = new LineString([[101.0, 0.0], [102.0, 1.0]]);

        $geometryCollection = new GeometryCollection([$point, $lineString]);

        $this->assertEquals($geojson, json_encode($geometryCollection));
    }

    public function testToWkt()
    {
        $wkt = 'GEOMETRYCOLLECTION(POINT(4 6),LINESTRING(4 6, 7 10))';

        $point      = new Point([4, 6]);
        $lineString = new LineString([[4, 6], [7, 10]]);

        $geometryCollection = new GeometryCollection([$point, $lineString]);

        $this->assertEquals($wkt, $geometryCollection->toWkt());
    }

    public function testFromGeoJson()
    {
        $json = '{ "type": "GeometryCollection",
    "geometries": [
      { "type": "Point",
        "coordinates": [100.0, 0.0]
        },
      { "type": "LineString",
        "coordinates": [ [101.0, 0.0], [102.0, 1.0] ]
        }
    ]
  }';

        $geomCollection = Location::fromGeoJson($json);

        $this->assertTrue($geomCollection instanceof GeometryCollection);
        $this->assertEquals([100.0, 0.0], $geomCollection[0]->toArray());
        $this->assertEquals([[101.0, 0.0], [102.0, 1.0]], $geomCollection[1]->toArray());
    }

    public function testFromWkt()
    {
        $wkt = 'GEOMETRYCOLLECTION(POINT(4 6),LINESTRING(4 6, 7 10))';

        $geomCollection = Location::fromWkt($wkt);

        $this->assertTrue($geomCollection instanceof GeometryCollection);
        $this->assertEquals([4, 6], $geomCollection[0]->toArray());
        $this->assertEquals([[4, 6], [7, 10]], $geomCollection[1]->toArray());
    }


}
