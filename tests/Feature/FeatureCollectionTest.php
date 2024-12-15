<?php

declare(strict_types=1);

namespace Ricklab\Location\Feature;

use PHPUnit\Framework\TestCase;
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
        $featureCollection = GeoJsonTransformer::decode($initialjson);

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

    public function testAddFeature(): void
    {
        $feature = new Feature(['name' => 'test']);
        $featureCollection = new FeatureCollection();
        $featureCollection = $featureCollection->withFeature($feature);

        $this->assertCount(1, $featureCollection->getFeatures());
        $this->assertSame($feature, $featureCollection->getFeatures()[0]);
    }

    public function testRemoveFeature(): void
    {
        $feature = new Feature(['name' => 'test']);
        $featureCollection = new FeatureCollection([$feature]);
        $featureCollection = $featureCollection->withoutFeature($feature);

        $this->assertCount(0, $featureCollection->getFeatures());
    }

    public function testGetFeatures(): void
    {
        $feature1 = new Feature(['name' => 'test1']);
        $feature2 = new Feature(['name' => 'test2']);
        $featureCollection = new FeatureCollection([$feature1, $feature2]);

        $features = $featureCollection->getFeatures();
        $this->assertCount(2, $features);
        $this->assertSame($feature1, $features[0]);
        $this->assertSame($feature2, $features[1]);
    }

    public function testJsonSerialize(): void
    {
        $feature = new Feature(['name' => 'test']);
        $featureCollection = new FeatureCollection([$feature]);
        $json = $featureCollection->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertSame('FeatureCollection', $json['type']);
        $this->assertCount(1, $json['features']);
    }
}
