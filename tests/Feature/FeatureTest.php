<?php

declare(strict_types=1);

namespace Ricklab\Location\Feature;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Geometry\BoundingBox;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\Point;
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
        $this->assertInstanceOf(BoundingBox::class, $feature->getBoundingBox());
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

        $this->assertJsonStringEqualsJsonString($initialjson, json_encode($feature));
    }

    public function testCreateWithExistingBoundingBox(): void
    {
        $bbox = new BoundingBox(-2, 50, 1, 52);
        $feature = Feature::createWithExistingBoundingBox($bbox, ['name' => 'test'], LineString::fromArray([[2, 3], [4, 5]]), 123);

        $this->assertInstanceOf(Feature::class, $feature);
        $this->assertSame($bbox, $feature->getBoundingBox());
        $this->assertSame('test', $feature->getProperties()['name']);
        $this->assertSame(123, $feature->getId());
    }

    public function testWithoutBbox(): void
    {
        $feature = new Feature([], LineString::fromArray([[2, 3], [4, 5]]), null, true);
        $featureWithoutBbox = $feature->withoutBbox();

        $this->assertNull($featureWithoutBbox->getBoundingBox());
    }

    public function testGetGeometry(): void
    {
        $point = new Point(1, 2);
        $feature = new Feature([], $point);

        $this->assertSame($point, $feature->getGeometry());
    }

    public function testWithGeometry(): void
    {
        $point = new Point(1, 2);
        $feature = new Feature();
        $featureWithGeometry = $feature->withGeometry($point);

        $this->assertSame($point, $featureWithGeometry->getGeometry());
    }

    public function testGetProperties(): void
    {
        $properties = ['name' => 'test'];
        $feature = new Feature($properties);

        $this->assertSame($properties, $feature->getProperties());
    }

    public function testWithProperties(): void
    {
        $properties = ['name' => 'test'];
        $feature = new Feature();
        $featureWithProperties = $feature->withProperties($properties);

        $this->assertSame($properties, $featureWithProperties->getProperties());
    }

    public function testGetBoundingBox(): void
    {
        $point = new Point(1, 2);
        $feature = new Feature([], $point, null, true);
        $bbox = $feature->getBoundingBox();

        $this->assertInstanceOf(BoundingBox::class, $bbox);
    }

    public function testJsonSerialize(): void
    {
        $feature = new Feature(['name' => 'test']);
        $json = $feature->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertSame('test', $json['properties']['name']);
    }

    public function testGetId(): void
    {
        $feature = new Feature([], null, 123);

        $this->assertSame(123, $feature->getId());
    }

    public function testWithId(): void
    {
        $feature = new Feature();
        $featureWithId = $feature->withId(123);

        $this->assertSame(123, $featureWithId->getId());
    }
}
