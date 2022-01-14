<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;

class MultiLineStringTest extends TestCase
{
    public function testToGeoJson(): void
    {
        $geojson = '{ "type": "MultiLineString",
    "coordinates": [
        [ [100.0, 0.0], [101.0, 1.0] ],
        [ [102.0, 2.0], [103.0, 3.0] ]
      ]
    }';

        $geojson = json_encode(json_decode($geojson));

        $lineString1 = new LineString([Point::fromArray([100.0, 0.0]), Point::fromArray([101.0, 1.0])]);
        $lineString2 = new LineString([Point::fromArray([102.0, 2.0]), Point::fromArray([103.0, 3.0])]);
        $multiLineString = new MultiLineString([$lineString1, $lineString2]);

        $this->assertEquals($geojson, json_encode($multiLineString));
    }

    public function testAddAndRemoveGeometries(): void
    {
        $lineString = new LineString([
            Point::fromArray([3, 4]),
            Point::fromArray([10, 50]),
            Point::fromArray([20, 25]),
        ]);

        $multiLineString = new MultiLineString([$lineString]);

        $lineString2 = new LineString([
            Point::fromArray([-5, -8]),
            Point::fromArray([-10, -8]),
            Point::fromArray([-15, -4]),
        ]);

        $multiLineString->addGeometry($lineString2);
        $this->assertContains($lineString, $multiLineString->getGeometries());
        $this->assertContains($lineString2, $multiLineString->getGeometries());
        $multiLineString->removeGeometry($lineString);
        $this->assertCount(1, $multiLineString->getGeometries());
        $this->assertNotContains($lineString, $multiLineString->getGeometries());
    }
}
