<?php
/**
 * Author: rick
 * Date: 05/01/2016
 * Time: 15:40
 */

namespace Ricklab\Location\Feature;


use Ricklab\Location\Location;

class FeatureCollectionTest extends \PHPUnit_Framework_TestCase
{

    public function testFromGeoJson()
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

        $this->assertTrue($featureCollection instanceof FeatureCollection);
        $this->assertEquals(json_encode(json_decode($initialjson)), json_encode($featureCollection));
    }

}
