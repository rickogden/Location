<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Transformer\GeoJsonTransformer;
use Ricklab\Location\Transformer\WktTransformer;

class GeometryCollectionTest extends TestCase
{
    protected GeometryCollection $collection;

    protected function setUp(): void
    {
        $point1 = new Point(-2.27354, 53.48575);
        $point2 = new Point(-2.23194, 53.48204);
        $this->collection = new GeometryCollection([$point1, $point2]);
    }

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
        $point = Point::fromArray([100.0, 0.0]);
        $lineString = LineString::fromArray([[101.0, 0.0], [102.0, 1.0]]);

        $geometryCollection = new GeometryCollection([$point, $lineString]);

        $this->assertJsonStringEqualsJsonString($json, json_encode($geometryCollection));
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

        /** @var GeometryCollection $geomCollection */
        $geomCollection = GeoJsonTransformer::decode($json);

        $this->assertInstanceOf(GeometryCollection::class, $geomCollection);
        $this->assertEquals([100.0, 0.0], $geomCollection->getGeometries()[0]->toArray());
        $this->assertEquals([[101.0, 0.0], [102.0, 1.0]], $geomCollection->getGeometries()[1]->toArray());
    }

    public function testFromWkt(): void
    {
        $wkt = 'GEOMETRYCOLLECTION(POINT(4 6),LINESTRING(4 6, 7 10))';

        /** @var GeometryCollection $geomCollection */
        $geomCollection = WktTransformer::decode($wkt);

        $this->assertInstanceOf(GeometryCollection::class, $geomCollection);
        $this->assertEquals([4, 6], $geomCollection->getGeometries()[0]->toArray());
        $this->assertEquals([[4, 6], [7, 10]], $geomCollection->getGeometries()[1]->toArray());
    }

    public function testFromArray(): void
    {
        $geometriesArray = [new Point(-2.27354, 53.48575), new Point(-2.23194, 53.48204)];
        $collection = GeometryCollection::fromArray($geometriesArray);

        $this->assertInstanceOf(GeometryCollection::class, $collection);
        $this->assertCount(2, $collection->getGeometries());
    }

    public function testJsonSerialize(): void
    {
        $json = json_encode($this->collection);
        $this->assertJson($json);
    }

    public function testToString(): void
    {
        $retval = '(POINT(-2.27354 53.48575), POINT(-2.23194 53.48204))';
        $this->assertEquals($retval, (string) $this->collection);
    }

    public function testWktFormat(): void
    {
        $retval = '(POINT(-2.27354 53.48575), POINT(-2.23194 53.48204))';
        $this->assertEquals($retval, $this->collection->wktFormat());
    }

    public function testGetGeometries(): void
    {
        $geometries = $this->collection->getGeometries();
        $this->assertCount(2, $geometries);
        $this->assertInstanceOf(Point::class, $geometries[0]);
        $this->assertInstanceOf(Point::class, $geometries[1]);
    }

    public function testWithGeometry(): void
    {
        $newPoint = new Point(-2.20000, 53.40000);
        $newCollection = $this->collection->withGeometry($newPoint);

        $this->assertCount(3, $newCollection->getGeometries());
        $this->assertEquals($newPoint, $newCollection->getGeometries()[2]);
    }

    public function testRemoveGeometry(): void
    {
        $pointToRemove = $this->collection->getGeometries()[0];
        $newCollection = $this->collection->removeGeometry($pointToRemove);

        $this->assertCount(1, $newCollection->getGeometries());
        $this->assertNotContains($pointToRemove, $newCollection->getGeometries());
    }
}
