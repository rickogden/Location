<?php
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 09:58
 */

namespace Ricklab\Location;

use Ricklab\Location\Ellipsoid\Earth;
use Ricklab\Location\Ellipsoid\Ellipsoid;
use Ricklab\Location\Feature\Feature;
use Ricklab\Location\Feature\FeatureAbstract;
use Ricklab\Location\Feature\FeatureCollection;
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

    const HAVERSINE = 1;

    const VINCENTY = 2;

    /**
     * @var bool Set to false if you have the pecl geospatial extension installed but do not want to use it
     */
    public static $useSpatialExtension = true;

    /**
     * @var int Set to either Location::HAVERSINE or Location::VICENTY. Defaults to Location::HAVERSINE
     */
    public static $defaultFormula = self::HAVERSINE;

    /**
     * @var Ellipsoid
     */
    protected static $ellipsoid;


    /**
     * Create a geometry from GeoJSON
     *
     * @param string|array|object $geojson the GeoJSON object either in a JSON string or a pre-parsed array/object
     *
     * @throws \ErrorException
     * @return GeometryInterface|FeatureAbstract
     */
    public static function fromGeoJson($geojson)
    {
        if (is_string($geojson)) {
            $geojson = json_decode($geojson, true);
        }

        if (is_object($geojson)) {
            $geojson = json_decode(json_encode($geojson), true);
        }

        $type = $geojson['type'];

        if ($type === 'GeometryCollection') {
            $geometries = [];
            foreach ($geojson['geometries'] as $geom) {
                $geometries[] = self::fromGeoJson($geom);
            }

            $geometry = self::createGeometry($type, $geometries);


        } elseif (strtolower($type) === 'feature') {

            $geometry = new Feature();
            if (isset( $geojson['geometry'] )) {
                $geometry->setGeometry(self::fromGeoJson($geojson['geometry']));
            }
            if (isset( $geojson['properties'] )) {
                $geometry->setProperties($geojson['properties']);
            }
        } elseif (strtolower($type) === 'featurecollection') {

            $geometry = new FeatureCollection();

            foreach ($geojson['features'] as $feature) {
                /** @noinspection PhpParamsInspection */
                $geometry->addFeature(self::fromGeoJson($feature));
            }

        } else {

            $coordinates = $geojson['coordinates'];
            $geometry    = self::createGeometry($type, $coordinates);
        }


        return $geometry;


    }

    /**
     * @param $type string the geometry type to create
     * @param $coordinates array the coordinates for the geometry type
     *
     * @return GeometryInterface
     */
    protected static function createGeometry($type, $coordinates)
    {
        switch (strtolower($type)) {
            case 'point':
                $result = new Point($coordinates);
                break;
            case 'linestring':
                $result = new LineString($coordinates);
                break;
            case 'polygon':
                $result = new Polygon($coordinates);
                break;
            case 'multipoint':
                $result = new MultiPoint($coordinates);
                break;
            case 'multilinestring':
                $result = new MultiLineString($coordinates);
                break;
            case 'multipolygon':
                $result = new MultiPolygon($coordinates);
                break;
            case 'geometrycollection':
                $result = new GeometryCollection($coordinates);
                break;
            default:
                throw new \InvalidArgumentException('This type is not supported');

        }

        return $result;
    }

    /**
     * Creates a geometry object from Well-Known Text
     *
     * @param string $wkt The WKT to create the geometry from
     *
     * @return GeometryInterface
     */
    public static function fromWkt($wkt)
    {

        $type = trim(substr($wkt, 0, strpos($wkt, '(')));
        $wkt  = trim(str_replace($type, '', $wkt));

        if (strtolower($type) === 'geometrycollection') {
            $geocol = preg_replace('/,?\s*([A-Za-z]+\()/', ':$1', $wkt);
            $geocol = trim($geocol);
            $geocol = preg_replace('/^\(/', '', $geocol);
            $geocol = preg_replace('/\)$/', '', $geocol);

            $arrays = [];
            foreach (explode(':', $geocol) as $subwkt) {
                if ($subwkt !== '') {
                    $arrays[] = self::fromWkt($subwkt);
                }
            }


        } else {

            $wkt = str_replace(', ', ',', $wkt);
            $wkt = str_replace(' ,', ',', $wkt);
            $wkt = str_replace('(', '[', $wkt);
            $wkt = str_replace(')', ']', $wkt);

            if (strtolower($type) === 'point') {
                $wkt = preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '$1, $2', $wkt);
            } else {
                $wkt = preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '[$1, $2]', $wkt);
            }
            $arrays = json_decode($wkt, true);

            if (strtolower($type) === 'multipoint') {
                foreach ($arrays as $index => $points) {
                    if (is_array($points[0])) {
                        $arrays[$index] = $points[0];
                    }
                }
            }
        }

        return self::createGeometry($type, $arrays);


    }

    /**
     * @param Point $point1 distance from this point
     * @param Point $point2 distance to this point
     * @param string $unit of measurement in which to return the result
     * @param null|int $formula formula to use, either Location::VINCENTY or Location::HAVERSINE. Defaults to
     * Location::$defaultFormula
     *
     * @return float
     */
    public static function calculateDistance(Point $point1, Point $point2, $unit, $formula = null)
    {
        if ($formula === null) {
            $formula = self::$defaultFormula;
        }
        if ($formula === self::VINCENTY) {
            $mDistance = self::vincenty($point1, $point2);
            if ($unit === 'm') {
                return $mDistance;
            } else {
                return self::convert($mDistance, 'm', $unit);
            }
        } else {
            $radDistance = self::haversine($point1, $point2);

            return $radDistance * self::getEllipsoid()->radius($unit);
        }
    }

    /**
     *
     * Vincenty formula for calculating distances
     *
     * @param Point $point1
     * @param Point $point2
     *
     * @return float distance in metres
     */
    public static function vincenty(Point $point1, Point $point2)
    {
        if (function_exists('vincenty') && self::$useSpatialExtension && self::getEllipsoid() instanceof Earth) {
            $from = $point1->jsonSerialize();
            $to   = $point2->jsonSerialize();

            $distance = vincenty($from, $to);

            return $distance;

        } else {
            $ellipsoid = self::getEllipsoid();

            $flattening = $ellipsoid->getFlattening();
            $U1         = atan(( 1.0 - $flattening ) * tan($point1->latitudeToRad()));
            $U2         = atan(( 1.0 - $flattening ) * tan($point2->latitudeToRad()));
            $L          = $point2->longitudeToRad() - $point1->longitudeToRad();
            $sinU1      = sin($U1);
            $cosU1      = cos($U1);
            $sinU2      = sin($U2);
            $cosU2      = cos($U2);
            $lambda     = $L;
            $looplimit  = 100;

            do {
                $sinLambda   = sin($lambda);
                $cosLambda   = cos($lambda);
                $sinSigma    = sqrt(pow($cosU2 * $sinLambda, 2) +
                                    pow($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda, 2));
                $cosSigma    = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
                $sigma       = atan2($sinSigma, $cosSigma);
                $sinAlpha    = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
                $cos2Alpha   = 1 - pow($sinAlpha, 2);
                $cosof2sigma = $cosSigma - 2 * $sinU1 * $sinU2 / $cos2Alpha;
                if ( ! is_numeric($cosof2sigma)) {
                    $cosof2sigma = 0;
                }
                $C       = $flattening / 16 * $cos2Alpha *
                           ( 4 + $flattening * ( 4 - 3 * $cos2Alpha ) );
                $lambdaP = $lambda;
                $lambda  = $L + ( 1 - $C ) * $flattening * $sinAlpha *
                                ( $sigma + $C * $sinSigma * ( $cosof2sigma + $C * $cosSigma * ( - 1 + 2 * pow(
                                                $cosof2sigma,
                                                2
                                            ) ) ) );

            } while (abs($lambda - $lambdaP) > 1e-12 && -- $looplimit > 0);

            $uSq        = $cos2Alpha * ( pow($ellipsoid->getMajorSemiAxis(), 2) - pow(
                        $ellipsoid->getMinorSemiAxis(),
                        2
                    ) ) / pow($ellipsoid->getMinorSemiAxis(), 2);
            $A          = 1 + $uSq / 16384 * ( 4096 + $uSq * ( - 768 + $uSq * ( 320 - 175 * $uSq ) ) );
            $B          = $uSq / 1024 * ( 256 + $uSq * ( - 128 + $uSq * ( 74 - 47 * $uSq ) ) );
            $deltaSigma = $B * $sinSigma * ( $cosof2sigma + $B / 4 * ( $cosSigma * ( - 1 + 2 * pow(
                                $cosof2sigma,
                                2
                            ) ) -
                                                                       $B / 6 * $cosof2sigma * ( - 3 + 4 * pow(
                                                                               $sinSigma,
                                                                               2
                                                                           ) ) * ( - 3 + 4 * pow(
                                                                               $cosof2sigma,
                                                                               2
                                                                           ) ) ) );
            $s          = $ellipsoid->getMinorSemiAxis() * $A * ( $sigma - $deltaSigma );
            $s          = floor($s * 1000) / 1000;

            return $s;
        }
    }

    /**
     * @return Earth|Ellipsoid the ellipsoid in use (generally Earth)
     */
    public static function getEllipsoid()
    {
        if (self::$ellipsoid === null) {
            self::$ellipsoid = new Earth;
        }

        return self::$ellipsoid;
    }

    /**
     * Set the ellipsoid to perform the calculations on
     *
     * @param Ellipsoid $ellipsoid
     */
    public static function setEllipsoid(Ellipsoid $ellipsoid)
    {

        self::$ellipsoid = $ellipsoid;

    }

    /**
     * Converts distances from one unit of measurement to another.
     *
     * @param $distance float the distance measurement
     * @param $from string the unit the distance measurement is in
     * @param $to string the unit the distance should be converted into
     *
     * @return float the distance in the new unit of measurement
     */
    public static function convert($distance, $from, $to)
    {

        $ellipsoid = self::getEllipsoid();

        $m = $distance / $ellipsoid->getMultiplier($from);

        return $m * $ellipsoid->getMultiplier($to);

    }

    /**
     * Uses the haversine formula to calculate the distance between 2 points.
     *
     * @param Point $point1
     * @param Point $point2
     *
     * @return float distance in radians
     */
    public static function haversine(Point $point1, Point $point2)
    {

        if (function_exists('haversine') && self::$useSpatialExtension) {
            $from = $point1->jsonSerialize();
            $to   = $point2->jsonSerialize();

            $radDistance = haversine($from, $to, 1);
        } else {
            $lat1 = $point1->latitudeToRad();
            $lon1 = $point1->longitudeToRad();
            $lat2 = $point2->latitudeToRad();
            $lon2 = $point2->longitudeToRad();

            $distanceLat  = $lat1 - $lat2;
            $distanceLong = $lon1 - $lon2;

            $radDistance = sin($distanceLat / 2) * sin($distanceLat / 2) +
                           cos($lat1) * cos($lat2) *
                           sin($distanceLong / 2) * sin($distanceLong / 2);
            $radDistance = 2 * atan2(sqrt($radDistance), sqrt(1 - $radDistance));
        }

        return $radDistance;

    }

    /**
     * @param Point $point the centre of the bounding box
     * @param number $radius minimum radius from $point
     * @param string $unit unit of the radius (default is kilometres)
     *
     * @return Polygon the BBox
     */
    public static function getBBoxByRadius(Point $point, $radius, $unit = 'km')
    {
        $north = $point->getRelativePoint($radius, 0, $unit);
        $south = $point->getRelativePoint($radius, 180, $unit);

        $limits['n'] = $north->getLatitude();
        $limits['s'] = $south->getLatitude();

        $radDist = $radius / Location::getEllipsoid()->radius($unit);
        //   $minLat  = deg2rad( $limits['s'] );
        //   $maxLat  = deg2rad( $limits['n'] );
        $radLon = $point->longitudeToRad();
        //if ($minLat > deg2rad(-90) && $maxLat < deg2rad(90)) {
        $deltaLon = asin(sin($radDist) / cos($point->latitudeToRad()));
        $minLon   = $radLon - $deltaLon;
        if ($minLon < deg2rad(- 180)) {
            $minLon += 2 * pi();
        }
        $maxLon = $radLon + $deltaLon;
        if ($maxLon > deg2rad(180)) {
            $maxLon -= 2 * pi();
        }
        //}

        $limits['w'] = rad2deg($minLon);
        $limits['e'] = rad2deg($maxLon);

        $nw      = new Point($limits['n'], $limits['w']);
        $ne      = new Point($limits['n'], $limits['e']);
        $sw      = new Point($limits['s'], $limits['w']);
        $se      = new Point($limits['s'], $limits['e']);
        $polygon = new Polygon([[$nw, $ne, $se, $sw]]);

        return $polygon;
    }


    /**
     * @param GeometryInterface|array $geometry either a geometry interface or an array of Geometries
     *
     * @return Polygon
     */
    public static function getBBox($geometry)
    {

        list( $minLon, $minLat, $maxLon, $maxLat ) = self::getBBoxArray($geometry);


        $nw = new Point([$minLon, $maxLat]);
        $ne = new Point([$maxLon, $maxLat]);
        $se = new Point([$maxLon, $minLat]);
        $sw = new Point([$minLon, $minLat]);

        return new Polygon([[$nw, $ne, $se, $sw]]);
    }

    /**
     * @param  GeometryInterface|array $geometry either a geometry interface or an array of Geometries
     *
     * @return array of coordinates in the order of: minimum longitude, minimum latitude, maximum longitude and maximum latitude
     */
    public static function getBBoxArray($geometry)
    {
        $maxLat = - 90;
        $minLat = 90;
        $maxLon = - 180;
        $minLon = 180;

        if (is_array($geometry)) {
            foreach ($geometry as $geom) {
                if ( ! $geom instanceof GeometryInterface) {
                    throw new \InvalidArgumentException('Array must contain GeometryInterface objects.');
                }
            }
            $points = $geometry;
        } elseif ($geometry instanceof GeometryInterface) {
            $points = $geometry->getPoints();
        } else {
            throw new \InvalidArgumentException('$geometry must be an array or instance of GeometryInterface.');
        }


        /** @var Point $point */
        foreach ($points as $point) {
            $maxLat = ( $point->getLatitude() > $maxLat ) ? $point->getLatitude() : $maxLat;
            $minLat = ( $point->getLatitude() < $minLat ) ? $point->getLatitude() : $minLat;
            $maxLon = ( $point->getLongitude() > $maxLon ) ? $point->getLongitude() : $maxLon;
            $minLon = ( $point->getLongitude() < $minLon ) ? $point->getLongitude() : $minLon;
        }

        return [$minLon, $minLat, $maxLon, $maxLat];
    }

    /**
     * @param int $degrees
     * @param int $minutes
     * @param float $seconds
     * @param null|string $direction use "S" for south and "W" for west. Defaults to East/North.
     *
     * @return float
     */
    public static function dmsToDecimal($degrees, $minutes, $seconds, $direction = null)
    {
        $decimal = $degrees + ( $minutes / 60 ) + ( $seconds / 3600 );

        if ($direction === 'S' || $direction === 'W') {
            $decimal *= - 1;
        }

        return $decimal;

    }

    /**
     * @param float $decimal the decimal longitude/latitude
     *
     * @return array of degrees, minutes, seconds from North/East.
     */
    public static function decimalToDms($decimal)
    {
        $deg = floor($decimal);
        $min = floor(( $decimal - $deg ) * 60);
        $sec = ( $decimal - $deg - $min / 60 ) * 3600;

        return [$deg, $min, $sec];
    }
}
