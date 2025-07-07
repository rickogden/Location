# Upgrade from Version 6 to Version 7

Basic usage should require minimal changes.

* PHP 8.1 minimum version
* BoundingBox is no longer a polygon, any polygon operations on bounding box use `getPolygon`.
* Geometry and feature classes are now all final, they should be decorated rather than extended.
* Numeric-strings are also valid for coordinates, for future calculator improvements (such as use of BC Math)
* Removed deprecated methods
* Calculator class methods are no longer static to allow for easier injection/replacement
* Calculator registry is used to store default calculators
* Improved type-safety
* Deprecated methods removed
* BoundingBox is no longer a Polygon (but `->getPolygon()` will create one from it)
* ENUMs are now used for Unit, and Degrees/Minutes/Seconds direction/axis

## New Features

### Well-Known Binary (WKB)

There is now a WKB transformer.

# Upgrading from Version 5 to Version 6

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

```injectablephp

use Ricklab\Location\Geometry\Geohash;
use Ricklab\Location\Geometry\Point;
$geohash1 = new Geohash('gbsuv7s0m');
$geohash2 = Geohash::fromPoint(new Point(-4.33387, 48.666751), 9);
$geohash1->equals($geohash2); // true
$geohash1->contains($geohash2);
$geohash3 = $geohash2->getAdjacentNorthEast(); // gets the next grid element.
```

### Degrees, Minutes, Seconds

This converter can be used to handle degrees, minutes, seconds with a direction and can be instantiated directly, or from a a string or deceminal (with an axis).

```injectablephp
use Ricklab\Location\Converter\DegreesMinutesSeconds;
use Ricklab\Location\Geometry\Point;

$dmsLatitude = DegreesMinutesSeconds::fromString('40° 26′ 46.2345″ S');
$dmsLongitude = DegreesMinutesSeconds::fromDecimal(23.32232, DegreesMinutesSeconds::AXIS_LONGITUDE);

$point = Point::fromDms($dmsLatitude, $dmsLongitude); // Because the direction is stored in the DMS object it doesn't matter on argument order.
```

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
use Ricklab\Location\Converter\NativeUnitConverter;

// Override the default calculator
$point1->distanceTo($point2, NativeUnitConverter::UNIT_METERS, new VincentyCalculator());

// Use the Vincenty Calculator as the default calculator
DefaultDistanceCalculator::setDefaultCalculator(new VincentyCalculator());

// Will now use the vincenty calculator
$point1->distanceTo($point2);
```

### Geospatial extension support

The `Location::$useGeospatialExtension` property has been removed, and can now be enabled or disabled per-calculator. It is recommended to leave it enabled unless you're experiencing issues.

```injectablephp
// Disable the geospatial extension for every use
use Ricklab\Location\Calculator\HaversineCalculator;HaversineCalculator::disableGeoSpatialExtension();
```

### Unit Converter

There is now a dedicated `UnitConverter` class, which handles all the unit conversions across the library. Additionally it contains the unit constants, which used to be in the `Location` class.

```injectablephp

// Deprecated
use Ricklab\Location\Converter\NativeUnitConverter;use Ricklab\Location\Location;Location::UNIT_METRES;

// From version 6
NativeUnitConverter::UNIT_METERS;
```

### Immutable Features

GeoJSON features are now immutable, which allows for caching of the calculations. This means the `set` methods have been replaced with `with` methods.

```injectablephp
$newFeature = $feature->withBbox();
```
