<?php

declare(strict_types=1);

namespace Ricklab\Location\Feature;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Location;

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
        $this->assertEquals(\json_encode(\json_decode($initialjson)), \json_encode($featureCollection));
    }
}
