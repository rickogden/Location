<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 09:58.
 */

namespace Ricklab\Location;

use Ricklab\Location\Calculator\BearingCalculator;
use Ricklab\Location\Calculator\DefaultDistanceCalculator;
use Ricklab\Location\Calculator\FractionAlongLineCalculator;
use Ricklab\Location\Calculator\HaversineCalculator;
use Ricklab\Location\Calculator\VincentyCalculator;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Ellipsoid\DefaultEllipsoid;
use Ricklab\Location\Ellipsoid\Earth;
use Ricklab\Location\Ellipsoid\Ellipsoid;
use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Exception\BoundBoxRangeException;
use Ricklab\Location\Feature\Feature;
use Ricklab\Location\Feature\FeatureCollection;
use Ricklab\Location\Geometry\BoundingBox;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\Polygon;
use Ricklab\Location\Transformer\GeoJsonTransformer;
use Ricklab\Location\Transformer\WktTransformer;

class Location
{
    public const FORMULA_HAVERSINE = 1;
    public const FORMULA_VINCENTY = 2;

    /** @deprecated use UnitConverter::UNIT_FEET */
    public const UNIT_FEET = UnitConverter::UNIT_FEET;

    /** @deprecated use UnitConverter::UNIT_KM */
    public const UNIT_KM = UnitConverter::UNIT_KM;

    /** @deprecated use UnitConverter::UNIT_METERS */
    public const UNIT_METRES = UnitConverter::UNIT_METERS;

    /** @deprecated use UnitConverter::UNIT_MILES */
    public const UNIT_MILES = UnitConverter::UNIT_MILES;

    /** @deprecated use UnitConverter::UNIT_NAUTICAL_MILES */
    public const UNIT_NAUTICAL_MILES = UnitConverter::UNIT_NAUTICAL_MILES;

    /** @deprecated use UnitConverter::UNIT_YARDS */
    public const UNIT_YARDS = UnitConverter::UNIT_YARDS;

    /**
     * @var bool Set to false if you have the pecl geospatial extension installed but do not want to use it
     */
    public static bool $useSpatialExtension = true;

    /**
     * @var int Set to either Location::HAVERSINE or Location::VICENTY. Defaults to Location::HAVERSINE
     */
    public static int $defaultFormula = self::FORMULA_HAVERSINE;
    protected static ?Ellipsoid $ellipsoid = null;

    /**
     * Create a geometry from GeoJSON.
     *
     * @param string|array|object $geojson the GeoJSON object either in a JSON string or a pre-parsed array/object
     *
     * @throws \ErrorException
     *
     * @return GeometryInterface|Feature|FeatureCollection
     *
     * @deprecated use GeoJsonFactory
     */
    public static function fromGeoJson($geojson)
    {
        if (\is_array($geojson)) {
            return GeoJsonTransformer::fromArray($geojson);
        }

        if (\is_string($geojson)) {
            return GeoJsonTransformer::decode($geojson);
        }

        if (\is_object($geojson)) {
            return GeoJsonTransformer::fromObject($geojson);
        }

        throw new \InvalidArgumentException('Must be an instance of array, object or string.');
    }

    /**
     * Creates a geometry object from Well-Known Text.
     *
     * @param string $wkt The WKT to create the geometry from
     *
     * @deprecated use WktTransformer::fromString()
     */
    public static function fromWkt(string $wkt): GeometryInterface
    {
        return WktTransformer::decode($wkt);
    }

    /**
     * @param Point    $point1  distance from this point
     * @param Point    $point2  distance to this point
     * @param string   $unit    of measurement in which to return the result
     * @param int|null $formula formula to use, either Location::VINCENTY or Location::HAVERSINE. Defaults to
     *                          Location::$defaultFormula
     *
     * @deprecated use the Point::distanceTo()
     */
    public static function calculateDistance(
        Point $point1,
        Point $point2,
        string $unit,
        ?int $formula = self::FORMULA_HAVERSINE
    ): float {
        if (null === $formula) {
            $formula = self::$defaultFormula;
        }

        if (self::FORMULA_VINCENTY === $formula) {
            $mDistance = VincentyCalculator::calculate($point1, $point2, DefaultEllipsoid::get());
        } else {
            $mDistance = HaversineCalculator::calculate($point1, $point2, DefaultEllipsoid::get());
        }

        return UnitConverter::convert($mDistance, UnitConverter::UNIT_METERS, $unit);
    }

    /**
     * @return Earth|EllipsoidInterface the ellipsoid in use (generally Earth)
     *
     * @deprecated use DefaultEllipsoid::get()
     */
    public static function getEllipsoid(): EllipsoidInterface
    {
        return DefaultEllipsoid::get();
    }

    /**
     * Set the ellipsoid to perform the calculations on.
     *
     * @deprecated use DefaultEllipsiod::set()
     */
    public static function setEllipsoid(Ellipsoid $ellipsoid): void
    {
        DefaultEllipsoid::set($ellipsoid);
    }

    /**
     * Converts distances from one unit of measurement to another.
     *
     * @param $distance float the distance measurement
     * @param $from string the unit the distance measurement is in
     * @param $to string the unit the distance should be converted into
     *
     * @return float the distance in the new unit of measurement
     *
     * @deprecated use UnitConverter::convert()
     */
    public static function convert(float $distance, string $from, string $to): float
    {
        return UnitConverter::convert($distance, $from, $to);
    }

    /**
     * @param Point  $point  the centre of the bounding box
     * @param float  $radius minimum radius from $point
     * @param string $unit   unit of the radius (default is kilometres)
     *
     * @throws BoundBoxRangeException
     *
     * @return BoundingBox the BBox
     *
     * @deprecated use BoundingBox::fromCenter() instead
     */
    public static function getBBoxByRadius(Point $point, float $radius, $unit = 'km'): BoundingBox
    {
        return BoundingBox::fromCenter($point, $radius, $unit);
    }

    /**
     * @deprecated use BoundingBox::fromGeometry() instead
     */
    public static function getBBox(GeometryInterface $geometry): Polygon
    {
        return BoundingBox::fromGeometry($geometry);
    }

    /**
     * @return array of coordinates in the order of: minimum longitude, minimum latitude, maximum longitude and maximum latitude
     *
     * @deprecated Use BoundingBox::fromGeometry($geometry)->getBounds() instead
     */
    public static function getBBoxArray(GeometryInterface $geometry): array
    {
        return BoundingBox::fromGeometry($geometry)->getBounds();
    }

    /**
     * @deprecated use BearingCalculator::calculateInitialBearing()
     */
    public static function getInitialBearing(Point $point1, Point $point2): float
    {
        return BearingCalculator::calculateInitialBearing($point1, $point2);
    }

    /**
     * @deprecated use BearingCalculator::calculateFinalBearing()
     */
    public static function getFinalBearing(Point $point1, Point $point2): float
    {
        return BearingCalculator::calculateFinalBearing($point1, $point2);
    }

    /**
     * @deprecated use FractionAlongLineCalculator::calculate()
     */
    public static function getFractionAlongLineBetween(Point $point1, Point $point2, float $fraction): Point
    {
        return FractionAlongLineCalculator::calculate(
            $point1,
            $point2,
            $fraction,
            DefaultDistanceCalculator::getDefaultCalculator(),
            DefaultEllipsoid::get()
        );
    }
}
