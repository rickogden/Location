# Upgrading to Version 6

Version 6 a plethora of new features.

## Requirements

Version 6 now requires PHP 7.4 or greater.

## New Features

### Bounding Box

Bounding box is an extension of Polygon which has exactly 4 sides. Because of these restrictions it has the following features:

* fromCenter: The ability to create a BoundingBox from a Point and a radius
* fromGeometry: The ability to create BoundingBox which perfect encompasses  the geometry
* Contains: whether a BoundingBox fully contains a geometry
* Intersects: whether a BoundingBox intersects a geometry

Additionally, all geometries now have a `getBbox()` method.

### Geo Hash

The GeoHash class can be used to handle Geohash strings, and allows conversion to and from geometries.

### Degrees, Minutes, Seconds

This converter will convert between decimal longitude and latitude and degrees, minutes, seconds.

## Deprecations

There are a fair few changes to the APIs, some things have not been able to be deprecated, in which case they have been removed/replaced.

### Location class

The static Location class has been deprecated in its entirety as they have been replaced by specific classes for more granular control over config.

### Distance calculators

The distance calculators have been extracted into individual classes. The `DefaultDistanceCalculator` can be statically updated to change the default distance calculator if none is provided.

The `Point::distanceTo()` method now takes an instance of `DistanceCalculator` as an optional parameter rather than a constant.

```injectablephp
use Ricklab\Location\Calculator\DefaultDistanceCalculator;
use Ricklab\Location\Calculator\VincentyCalculator;
use Ricklab\Location\Converter\UnitConverter;

// Override the default calculator
$point1->distanceTo($point2, UnitConverter::UNIT_METERS, new VincentyCalculator());

// Use the Vincenty Calculator as the default calculator
DefaultDistanceCalculator::setDefaultCalculator(new VincentyCalculator());

// Will now use the vincenty calculator
$point1->distanceTo($point2);
```

### Geospatial extension support

The `Location::$useGeospatialExtension` property has been removed, and can now be enabled or disabled per-calculator. It is recommended to leave it enabled unless you're experiencing issues.

```injectablephp
// Disable the geospatial extension for every use
\Ricklab\Location\Calculator\HaversineCalculator::disableGeoSpatialExtension();
```

### Unit Converter

There is now a dedicated `UnitConverter` class, which handles all the unit conversions across the library. Additionally it contains the unit constants, which used to be in the `Location` class.

```injectablephp

// Deprecated
\Ricklab\Location\Location::UNIT_METRES;

// From version 6
\Ricklab\Location\Converter\UnitConverter::UNIT_METERS;
```

### Immutable Features

GeoJSON features are now immutable, which allows for caching of the calculations. This means the `set` methods have been replaced with `with` methods.

```injectablephp
/** @var \Ricklab\Location\Feature\Feature */
$newFeature = $feature->withBbox();
```