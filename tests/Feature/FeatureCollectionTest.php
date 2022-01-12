<?php

declare(strict_types=1);

namespace Ricklab\Location\Feature;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Location;
use Ricklab\Location\Transformer\GeoJsonTransformer;

class FeatureCollectionTest extends TestCase
{
    public function testFromGeoJson(): void
    {
        $initialjson = '{ "type": "FeatureCollection",
    "features": [
      { "type": "Feature",
    "geometry": {
      "type": "Polygon",
      "coordinates": [[
        [-10.0, -10.0], [10.0, -10.0], [10.0, 10.0], [-10.0, 10.0], [-10,-10]
        ]]
      },
      "properties": {
        "foo":"bar"
      }
    }
      ]
    }';
        /** @var FeatureCollection $featureCollection */
        $featureCollection = Location::fromGeoJson($initialjson);

        $this->assertInstanceOf(FeatureCollection::class, $featureCollection);
        $result = json_encode($featureCollection);
        $this->assertJsonStringEqualsJsonString($initialjson, json_encode($featureCollection));
    }

    public function testFromString(): void
    {
        $initialjson = '{ "type": "FeatureCollection",
    "features": [
      { "type": "Feature",
    "geometry": {
      "type": "Polygon",
      "coordinates": [[
        [-10.0, -10.0], [10.0, -10.0], [10.0, 10.0], [-10.0, 10.0], [-10,-10]
        ]]
      },
      "properties": {
        "foo":"bar"
      }
    }
      ]
    }';
        /** @var FeatureCollection $featureCollection */
        $featureCollection = GeoJsonTransformer::decode($initialjson);

        $this->assertInstanceOf(FeatureCollection::class, $featureCollection);
        $this->assertJsonStringEqualsJsonString($initialjson, json_encode($featureCollection));
    }

    public function testFromWithBbox(): void
    {
        $initialjson = '{ "type": "FeatureCollection",
    "features": [
      { "type": "Feature",
    "geometry": {
      "type": "Polygon",
      "coordinates": [[
        [-10.0, -10.0], [10.0, -10.0], [10.0, 10.0], [-10.0, 10.0], [-10,-10]
        ]]
      },
      "properties": {
        "foo":"bar"
      }
    }
      ]
    }';
        $jsonWithBbox = '{ "type": "FeatureCollection",
        "bbox": [-10, -10, 10, 10],
    "features": [
      { "type": "Feature",
    "geometry": {
      "type": "Polygon",
      "coordinates": [[
        [-10.0, -10.0], [10.0, -10.0], [10.0, 10.0], [-10.0, 10.0], [-10,-10]
        ]]
      },
      "properties": {
        "foo":"bar"
      }
    }
      ]
    }';

        /** @var FeatureCollection $featureCollection */
        $featureCollection = GeoJsonTransformer::decode($initialjson)->withBbox();

        $this->assertInstanceOf(FeatureCollection::class, $featureCollection);
        $this->assertJsonStringEqualsJsonString($jsonWithBbox, json_encode($featureCollection));
    }
}
