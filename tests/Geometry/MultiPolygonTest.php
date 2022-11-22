<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;

class MultiPolygonTest extends TestCase
{
    public function testToGeoJson(): void
    {
        $geojson = '{ "type": "MultiPolygon",
    "coordinates": [
        [ [ [100.0, 0.0], [101.0, 1.0], [100.0, 0.0] ] ],
        [ [ [102.0, 2.0], [103.0, 3.0], [102.0, 2.0] ] ]
      ]
    }';

        $geojson = json_encode(json_decode($geojson));

        $polygon1 = new Polygon([new LineString([Point::fromArray([100.0, 0.0]), Point::fromArray([101.0, 1.0])])]);
        $polygon2 = new Polygon([new LineString([Point::fromArray([102.0, 2.0]), Point::fromArray([103.0, 3.0])])]);
        $multiPolygon = new MultiPolygon([$polygon1, $polygon2]);

        $this->assertJsonStringEqualsJsonString($geojson, json_encode($multiPolygon));
    }

    public function testAddAndRemoveGeometries(): void
    {
        $polygon = new Polygon([new LineString([
            Point::fromArray([3, 4]),
            Point::fromArray([10, 50]),
            Point::fromArray([20, 25]),
        ])]);

        $multiPolygon = new MultiPolygon([$polygon]);

        $polygon2 = new Polygon([new LineString([
            Point::fromArray([-5, -8]),
            Point::fromArray([-10, -8]),
            Point::fromArray([-15, -4]),
        ])]);

        $multiPolygon = $multiPolygon->withGeometry($polygon2);
        $this->assertContains($polygon, $multiPolygon->getGeometries());
        $this->assertContains($polygon2, $multiPolygon->getGeometries());
        $multiPolygon = $multiPolygon->withoutGeometry($polygon);
        $this->assertCount(1, $multiPolygon->getGeometries());
        $this->assertNotContains($polygon, $multiPolygon->getGeometries());
    }
}
