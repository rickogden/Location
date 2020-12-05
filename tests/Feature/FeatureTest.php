<?php

declare(strict_types=1);

namespace Ricklab\Location\Feature;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\Polygon;
use Ricklab\Location\Transformer\GeoJsonTransformer;

class FeatureTest extends TestCase
{
    public function testBBox(): void
    {
        $line = LineString::fromArray([[2, 3], [4, 5]]);
        $feature = new Feature();
        $feature = $feature->withGeometry($line)->withBbox();

        $json = $feature->jsonSerialize();
        $this->assertIsArray($json['bbox']);
    }

    public function testGeoJson(): void
    {
        $initialjson = '{ "type": "Feature",
    "bbox": [-10.0, -10.0, 10.0, 10.0],
    "geometry": {
      "type": "Polygon",
      "coordinates": [[
        [-10.0, -10.0], [10.0, -10.0], [10.0, 10.0], [-10.0, 10.0], [-10,-10]
        ]]
      },
      "properties": {
        "foo":"bar"
      }
    }';
        /** @var Feature $feature */
        $feature = GeoJsonTransformer::decode($initialjson);
        $this->assertInstanceOf(Feature::class, $feature);
        $this->assertInstanceOf(Polygon::class, $feature->getGeometry());
        $this->assertEquals('bar', $feature->getProperties()['foo']);

        $this->assertJsonStringEqualsJsonString($initialjson, \json_encode($feature));
    }
}
