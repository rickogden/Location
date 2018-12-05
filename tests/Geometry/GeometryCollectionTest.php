<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Location;

class GeometryCollectionTest extends TestCase
{
    public function testToGeoJson(): void
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

    public function testToWkt(): void
    {
        $wkt = 'GEOMETRYCOLLECTION(POINT(4 6), LINESTRING(4 6, 7 10))';

        $point = Point::fromArray([4, 6]);
        $lineString = LineString::fromArray([[4, 6], [7, 10]]);

        $geometryCollection = new GeometryCollection([$point, $lineString]);

        $this->assertEquals($wkt, $geometryCollection->toWkt());
    }

    public function testFromGeoJson(): void
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

        $this->assertInstanceOf(GeometryCollection::class, $geomCollection);
        $this->assertEquals([100.0, 0.0], $geomCollection->getGeometries()[0]->toArray());
        $this->assertEquals([[101.0, 0.0], [102.0, 1.0]], $geomCollection->getGeometries()[1]->toArray());
    }

    public function testFromWkt(): void
    {
        $wkt = 'GEOMETRYCOLLECTION(POINT(4 6),LINESTRING(4 6, 7 10))';

        $geomCollection = Location::fromWkt($wkt);

        $this->assertInstanceOf(GeometryCollection::class, $geomCollection);
        $this->assertEquals([4, 6], $geomCollection->getGeometries()[0]->toArray());
        $this->assertEquals([[4, 6], [7, 10]], $geomCollection->getGeometries()[1]->toArray());
    }
}
