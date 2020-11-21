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
use Ricklab\Location\Calculator\UnitConverter;
use Ricklab\Location\Calculator\VincentyCalculator;
use Ricklab\Location\Ellipsoid\Earth;
use Ricklab\Location\Ellipsoid\Ellipsoid;
use Ricklab\Location\Exception\BoundBoxRangeException;
use Ricklab\Location\Feature\Feature;
use Ricklab\Location\Feature\FeatureAbstract;
use Ricklab\Location\Feature\FeatureCollection;
use Ricklab\Location\Geometry\BoundingBox;
use Ricklab\Location\Geometry\GeometryCollection;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\MultiLineString;
use Ricklab\Location\Geometry\MultiPoint;
use Ricklab\Location\Geometry\MultiPolygon;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\Polygon;

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
     * @return GeometryInterface|FeatureAbstract
     */
    public static function fromGeoJson($geojson)
    {
        if (\is_string($geojson)) {
            $geojson = \json_decode($geojson, true);
        }

        if (\is_object($geojson)) {
            $geojson = \json_decode(\json_encode($geojson), true);
        }

        $type = $geojson['type'];

        if ('GeometryCollection' === $type) {
            $geometries = [];
            foreach ($geojson['geometries'] as $geom) {
                $geometries[] = self::fromGeoJson($geom);
            }

            $geometry = self::createGeometry($type, $geometries);
        } elseif ('feature' === \mb_strtolower($type)) {
            $geometry = new Feature();

            if (isset($geojson['geometry'])) {
                $decodedGeo = self::fromGeoJson($geojson['geometry']);

                if ($decodedGeo instanceof GeometryInterface) {
                    $geometry->setGeometry($decodedGeo);
                }
            }

            if (isset($geojson['properties'])) {
                $geometry->setProperties($geojson['properties']);
            }
        } elseif ('featurecollection' === \mb_strtolower($type)) {
            $geometry = new FeatureCollection();

            foreach ($geojson['features'] as $feature) {
                $decodedFeature = self::fromGeoJson($feature);

                if ($decodedFeature instanceof Feature) {
                    $geometry->addFeature($decodedFeature);
                }
            }
        } else {
            $coordinates = $geojson['coordinates'];
            $geometry = self::createGeometry($type, $coordinates);
        }

        return $geometry;
    }

    /**
     * @param $type string the geometry type to create
     * @param $coordinates array the coordinates for the geometry type
     */
    protected static function createGeometry(string $type, array $coordinates): GeometryInterface
    {
        switch (\mb_strtolower($type)) {
            case 'point':
                $result = Point::fromArray($coordinates);
                break;
            case 'linestring':
                $result = LineString::fromArray($coordinates);
                break;
            case 'polygon':
                $result = Polygon::fromArray($coordinates);
                break;
            case 'multipoint':
                $result = MultiPoint::fromArray($coordinates);
                break;
            case 'multilinestring':
                $result = MultiLineString::fromArray($coordinates);
                break;
            case 'multipolygon':
                $result = MultiPolygon::fromArray($coordinates);
                break;
            case 'geometrycollection':
                $result = GeometryCollection::fromArray($coordinates);
                break;
            default:
                throw new \InvalidArgumentException('This type is not supported');
        }

        return $result;
    }

    /**
     * Creates a geometry object from Well-Known Text.
     *
     * @param string $wkt The WKT to create the geometry from
     */
    public static function fromWkt(string $wkt): GeometryInterface
    {
        $type = \trim(\mb_substr($wkt, 0, \mb_strpos($wkt, '(') ?: 0));
        $wkt = \trim(\str_replace($type, '', $wkt));

        if ('geometrycollection' === \mb_strtolower($type)) {
            $geocol = \preg_replace('/,?\s*([A-Za-z]+\()/', ':$1', $wkt);
            $geocol = \trim($geocol);
            $geocol = \preg_replace('/^\(/', '', $geocol);
            $geocol = \preg_replace('/\)$/', '', $geocol);

            $arrays = [];
            foreach (\explode(':', $geocol) as $subwkt) {
                if ('' !== $subwkt) {
                    $arrays[] = self::fromWkt($subwkt);
                }
            }
        } else {
            $json = \str_replace([', ', ' ,', '(', ')'], [',', ',', '[', ']'], $wkt);

            if ('point' === \mb_strtolower($type)) {
                $json = \preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '$1, $2', $json);
            } else {
                $json = \preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '[$1, $2]', $json);
            }

            if (null === $json) {
                throw new \InvalidArgumentException('This is not recognised WKT.');
            }
            $arrays = \json_decode($json, true);

            if (!$arrays) {
                throw new \InvalidArgumentException('This is not recognised WKT.');
            }

            if ('multipoint' === \mb_strtolower($type)) {
                foreach ($arrays as $index => $points) {
                    if (\is_array($points[0])) {
                        $arrays[$index] = $points[0];
                    }
                }
            }
        }

        return self::createGeometry($type, $arrays);
    }

    /**
     * @param Point    $point1  distance from this point
     * @param Point    $point2  distance to this point
     * @param string   $unit    of measurement in which to return the result
     * @param int|null $formula formula to use, either Location::VINCENTY or Location::HAVERSINE. Defaults to
     *                          Location::$defaultFormula
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
            $mDistance = VincentyCalculator::calculate($point1, $point2, self::getEllipsoid());
        } else {
            $mDistance = HaversineCalculator::calculate($point1, $point2, self::getEllipsoid());
        }

        return UnitConverter::convert($mDistance, self::UNIT_METRES, $unit);
    }

    /**
     * @return Earth|Ellipsoid the ellipsoid in use (generally Earth)
     */
    public static function getEllipsoid(): Ellipsoid
    {
        if (null === self::$ellipsoid) {
            self::$ellipsoid = new Earth();
        }

        return self::$ellipsoid;
    }

    /**
     * Set the ellipsoid to perform the calculations on.
     */
    public static function setEllipsoid(Ellipsoid $ellipsoid): void
    {
        self::$ellipsoid = $ellipsoid;
    }

    /**
     * Converts distances from one unit of measurement to another.
     *
     * @deprecated use UnitConverter::convert()
     *
     * @param $distance float the distance measurement
     * @param $from string the unit the distance measurement is in
     * @param $to string the unit the distance should be converted into
     *
     * @return float the distance in the new unit of measurement
     */
    public static function convert(float $distance, string $from, string $to): float
    {
        return UnitConverter::convert($distance, $from, $to);
    }

    /**
     * @deprecated use BoundingBox::fromCenter() instead
     *
     * @param Point  $point  the centre of the bounding box
     * @param float  $radius minimum radius from $point
     * @param string $unit   unit of the radius (default is kilometres)
     *
     * @throws BoundBoxRangeException
     *
     * @return BoundingBox the BBox
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
     * @deprecated Use BoundingBox::fromGeometry($geometry)->getBounds() instead
     *
     * @return array of coordinates in the order of: minimum longitude, minimum latitude, maximum longitude and maximum latitude
     */
    public static function getBBoxArray(GeometryInterface $geometry): array
    {
        return BoundingBox::fromGeometry($geometry)->getBounds();
    }

    /**
     * @param string|null $direction use "S" for south and "W" for west. Defaults to East/North.
     */
    public static function dmsToDecimal(int $degrees, int $minutes, float $seconds, ?string $direction = null): float
    {
        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ('S' === $direction || 'W' === $direction) {
            $decimal *= -1;
        }

        return $decimal;
    }

    /**
     * @param float $decimal the decimal longitude/latitude
     *
     * @return array{0: int, 1: int, 2: float} of degrees, minutes, seconds from North/East
     */
    public static function decimalToDms(float $decimal): array
    {
        $deg = (int) \floor($decimal);
        $min = (int) \floor(($decimal - $deg) * 60);
        $sec = ($decimal - $deg - $min / 60) * 3600;

        return [$deg, $min, $sec];
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
            self::getEllipsoid()
        );
    }
}
