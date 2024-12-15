# PHP Location Library [![CI](https://github.com/rickogden/Location/actions/workflows/ci.yaml/badge.svg)](https://github.com/rickogden/Location/actions/workflows/ci.yaml)

A library for geospatial calculations in PHP.

## Installation

Using composer, run `composer require ricklab/location`

## Usage

A brief example of how this library can be used:

```php
use Ricklab\Location\Calculator\VincentyCalculator;use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\BoundingBox;

// Usage of Point
$point = new Point($longitude, $latitude);
$point2 = new Point($lon2, $lat2);

// Calculate distance between two points in miles using the Vincenty formula
$distance = $point->distanceTo($point2, UnitConverter::UNIT_MILES, new VincentyCalculator());

// Usage of LineString
$line = new LineString([$point, $point2]);

// Usage of bounding box
$bbox = BoundingBox::fromCenter($point, 1000); // 1000 meters radius
$bbox2 = BoundingBox::fromGeometry($line); // Bounding box of the line
$contains = $bbox2->contains($point2); // true if the point is within the bounding box
```

## Transformers

Transformers are used to convert geometries to and from various formats. This library includes a GeoJSON and a WKT transformer.

### GeoJsonTransformer

The `GeoJsonTransformer` class converts geometries into GeoJSON format.

##### Usage

Here is an example of how to use the `GeoJsonTransformer` class:

```php
use Ricklab\Location\Transformer\GeoJsonTransformer;
use Ricklab\Location\Geometry\Point;

$geometry = new Point($longitude, $latitude);
$geoJsonString = GeoJsonTransformer::encode($geometry);

$secondGeometry = GeoJsonTransformer::decode($geoJsonString);

echo $geoJsonString;
```

### WktTransformer

The `WktTransformer` class converts geometries into WKT format.

### Usage

Here is an example of how to use the `WktTransformer` class:

```php
use Ricklab\Location\Transformer\WktTransformer;
use Ricklab\Location\Geometry\Point;

$geometry = new Point($longitude, $latitude);
$wktString = WktTransformer::encode($geometry);

echo $wktString;
```

## Features

The `Feature` class represents a single geospatial feature from the GeoJSON spec.

### Usage

Here is an example of how to use the `Feature` class:

```php
use Ricklab\Location\Feature\Feature;
use Ricklab\Location\Geometry\Point;

$geometry = new Point($longitude, $latitude);
$feature = new Feature(['property-name', 'property-value'], $geometry);

$geometry = $feature->getGeometry();
$properties = $feature->getProperties();
```

## FeatureCollection

The `FeatureCollection` class represents a collection of geospatial features.

### Usage

Here is an example of how to use the `FeatureCollection` class:

```php
use Ricklab\Location\Feature\Feature;
use Ricklab\Location\Feature\FeatureCollection;
use Ricklab\Location\Geometry\Point;use Ricklab\Location\Transformer\GeoJsonTransformer;

$feature1 = new Feature(['name' => 'Feature 1'], new Point($longitude1, $latitude1));
$feature2 = new Feature(['name' => 'Feature 1'], new Point($longitude2, $latitude2));

$collection = new FeatureCollection([$feature1, $feature2], true);

$features = $collection->getFeatures();
$bbox = $collection->getBbox(); // Returns the bounding box of the collection
$featureGeoJson = GeoJsonTransformer::encode($features); // GeoJSON feature collection representation
```
