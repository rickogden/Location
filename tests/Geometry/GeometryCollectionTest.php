<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 03/12/2015
 * Time: 11:10.
 */

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Location;

class GeometryCollectionTest extends TestCase
{
    public function testToGeoJson()
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
        $geojson = \json_encode(\json_decode($json, true));

        $point = Point::fromArray([100.0, 0.0]);
        $lineString = LineString::fromArray([[101.0, 0.0], [102.0, 1.0]]);

        $geometryCollection = new GeometryCollection([$point, $lineString]);

        $this->assertEquals($geojson, \json_encode($geometryCollection));
    }

    public function testToWkt()
    {
        $wkt = 'GEOMETRYCOLLECTION(POINT(4 6), LINESTRING(4 6, 7 10))';

        $point = Point::fromArray([4, 6]);
        $lineString = LineString::fromArray([[4, 6], [7, 10]]);

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
        $this->assertEquals([100.0, 0.0], $geomCollection->getGeometries()[0]->toArray());
        $this->assertEquals([[101.0, 0.0], [102.0, 1.0]], $geomCollection->getGeometries()[1]->toArray());
    }

    public function testFromWkt()
    {
        $wkt = 'GEOMETRYCOLLECTION(POINT(4 6),LINESTRING(4 6, 7 10))';

        $geomCollection = Location::fromWkt($wkt);

        $this->assertTrue($geomCollection instanceof GeometryCollection);
        $this->assertEquals([4, 6], $geomCollection->getGeometries()[0]->toArray());
        $this->assertEquals([[4, 6], [7, 10]], $geomCollection->getGeometries()[1]->toArray());
    }
}
