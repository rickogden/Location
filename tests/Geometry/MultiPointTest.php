<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Transformer\GeoJsonTransformer;

class MultiPointTest extends TestCase
{
    public function testGeoJson(): void
    {
        $geojson = '{ "type": "MultiPoint",
    "coordinates": [ [100.0, 0.0], [101.0, 1.0] ]
    }';

        $multipoint = GeoJsonTransformer::decode($geojson);

        $this->assertInstanceOf(MultiPoint::class, $multipoint);

        $this->assertEquals([100.0, 0.0], $multipoint->getGeometries()[0]->toArray());
        $this->assertEquals([101.0, 1.0], $multipoint->getGeometries()[1]->toArray());

        $geojson = json_encode(json_decode($geojson));

        $this->assertEquals($geojson, json_encode($multipoint));
    }

    public function testEquals(): void
    {
        $original = [[-2.27354, 53.48575], [-2.23194, 53.48204]];

        $multiPoint = MultiPoint::fromArray($original);
        $multiPoint2 = MultiPoint::fromArray($original);
        $this->assertTrue($multiPoint->equals($multiPoint2));
    }

    public function testNotEquals(): void
    {
        $original = [[-2.27354, 53.48575], [-2.23194, 53.48204]];

        $multiPoint = MultiPoint::fromArray($original);
        $multiPoint2 = MultiPoint::fromArray(array_reverse($original));
        $this->assertFalse($multiPoint->equals($multiPoint2));

        $newGeom = $original;
        $newGeom[] = [-2.23124, 53.48214];
        $multiPoint2 = MultiPoint::fromArray($newGeom);
        $this->assertFalse($multiPoint->equals($multiPoint2));

        $polygon = new Polygon([new LineString($multiPoint->getPoints())]);
        $this->assertFalse($multiPoint->equals($polygon));

        $lineString = new LineString($multiPoint->getPoints());
        $this->assertFalse($multiPoint->equals($lineString));
    }
}
