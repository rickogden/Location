<?php
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 09:58
 */

namespace Ricklab\Location;

use Ricklab\Location\Ellipsoid\Earth;
use Ricklab\Location\Ellipsoid\Ellipsoid;

class Location
{


    const HAVERSINE = 1;

    const VINCENTY = 2;

    /**
     * @var Ellipsoid
     */
    protected static $ellipsoid;

    /**
     * @var bool Set to false if you have the pecl geospatial extension installed but do not want to use it
     */
    public static $useSpatialExtension = true;

    /**
     * @var bool Set to true to use Vincenty as distance calculator, set to either Location::HAVERSINE or Location::VICENTY
     */
    public static $defaultFormula = self::HAVERSINE;

    /**
     * Set the ellipsoid to perform the calculations on
     *
     * @param Ellipsoid $ellipsoid
     */
    public static function setEllipsoid( Ellipsoid $ellipsoid )
    {

        self::$ellipsoid = $ellipsoid;

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
     * @param $geojson
     *
     * @throws \ErrorException
     * @return \Ricklab\Location\Geometry
     */
    public static function fromGeoJson( $geojson )
    {
        if (is_string( $geojson )) {
            $geojson = json_decode( $geojson, true );
        }

        if (is_object( $geojson )) {
            $geojson = json_decode( json_encode( $geojson ), true );
        }


        $type     = $geojson['type'];
        $coordinates = $geojson['coordinates'];
        $geometry = self::createGeometry( $type, $coordinates );

        return $geometry;


    }

    protected static function createGeometry( $type, array $coordinates )
    {
        switch ($type) {
            case 'Point':
                $result = new Point( $coordinates );
                break;

            case 'LineString':
                $points = array();
                foreach ($coordinates as $coordinate) {
                    $points[] = new Point( $coordinate );
                }
                if (count( $points ) > 1) {
                    $result = new LineString( $points );
                } else {
                    throw new \ErrorException( 'cannot parse as Line' );
                }
                break;
            case 'Polygon':
                $polygons = array();
                foreach ($coordinates as $polygon) {
                    $points = [ ];
                    foreach ($polygon as $coordinate) {
                        if (is_array( $coordinate )) {
                            $points[] = new Point( $coordinate );
                        }
                    }
                    $polygons[] = $points;
                }

                $result = new Polygon( $polygons );

                break;

        }

        if ( ! isset( $result )) {
            throw new \InvalidArgumentException( 'This type of geojson is not supported' );
        }

        return $result;
    }

    /**
     * Uses the haversine formula to calculate the distance between 2 points.
     *
     * @param Point $point1
     * @param Point $point2
     *
     * @return float distance in radians
     */
    public static function haversine( Point $point1, Point $point2 )
    {

        if (function_exists( 'haversine' ) && self::$useSpatialExtension) {
            $from = $point1->jsonSerialize();
            $to   = $point2->jsonSerialize();

            $radDistance = haversine( $from, $to ) / 6378137;
        } else {
            $distanceLat  = $point1->latitudeToRad() - $point2->latitudeToRad();
            $distanceLong = $point1->longitudeToRad() - $point2->longitudeToRad();

            $radDistance = sin( $distanceLat / 2 ) * sin( $distanceLat / 2 ) +
                           cos( $point1->latitudeToRad() ) * cos( $point2->latitudeToRad() ) *
                           sin( $distanceLong / 2 ) * sin( $distanceLong / 2 );
            $radDistance = 2 * atan2( sqrt( $radDistance ), sqrt( 1 - $radDistance ) );
        }

        return $radDistance;

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
    public static function vincenty( Point $point1, Point $point2 )
    {
        if (function_exists( 'vincenty' ) && self::$useSpatialExtension && self::getEllipsoid() instanceof Earth) {

            $from = $point1->jsonSerialize();
            $to   = $point2->jsonSerialize();

            $distance = vincenty( $from, $to );

            return $distance;

        } else {
            $ellipsoid = self::getEllipsoid();

            $U1 = atan( ( 1.0 - $ellipsoid->getFlattening() ) * tan( $point1->latitudeToRad() ) );
            $U2 = atan( ( 1.0 - $ellipsoid->getFlattening() ) * tan( $point2->latitudeToRad() ) );
            $L             = $point2->longitudeToRad() - $point1->longitudeToRad();
            $sinU1         = sin( $U1 );
            $cosU1         = cos( $U1 );
            $sinU2         = sin( $U2 );
            $cosU2         = cos( $U2 );
            $lambda        = $L;
            $looplimit = 100;

            do {
                $sinLambda   = sin( $lambda );
                $cosLambda   = cos( $lambda );
                $sinSigma  = sqrt( pow( $cosU2 * $sinLambda, 2 ) +
                                   pow( $cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda, 2 ) );
                $cosSigma    = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
                $sigma       = atan2( $sinSigma, $cosSigma );
                $sinAlpha    = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
                $cos2Alpha = 1 - pow( $sinAlpha, 2 );
                $cosof2sigma = $cosSigma - 2 * $sinU1 * $sinU2 / $cos2Alpha;
                if ( ! is_numeric( $cosof2sigma )) {
                    $cosof2sigma = 0;
                }
                $C      = $ellipsoid->getFlattening() / 16 * $cos2Alpha * ( 4 + $ellipsoid->getFlattening() * ( 4 - 3 * $cos2Alpha ) );
                $lambdaP = $lambda;
                $lambda = $L + ( 1 - $C ) * $ellipsoid->getFlattening() * $sinAlpha *
                               ( $sigma + $C * $sinSigma * ( $cosof2sigma + $C * $cosSigma * ( - 1 + 2 * pow( $cosof2sigma,
                                               2 ) ) ) );

            } while (abs( $lambda - $lambdaP ) > 1e-12 && -- $looplimit > 0);

            $uSq = $cos2Alpha * ( pow( $ellipsoid->getMajorSemiAxis(), 2 ) - pow( $ellipsoid->getMinorSemiAxis(),
                        2 ) ) / pow( $ellipsoid->getMinorSemiAxis(), 2 );
            $A          = 1 + $uSq / 16384 * ( 4096 + $uSq * ( - 768 + $uSq * ( 320 - 175 * $uSq ) ) );
            $B          = $uSq / 1024 * ( 256 + $uSq * ( - 128 + $uSq * ( 74 - 47 * $uSq ) ) );
            $deltaSigma = $B * $sinSigma * ( $cosof2sigma + $B / 4 * ( $cosSigma * ( - 1 + 2 * pow( $cosof2sigma,
                                2 ) ) -
                                                                       $B / 6 * $cosof2sigma * ( - 3 + 4 * pow( $sinSigma,
                                                                               2 ) ) * ( - 3 + 4 * pow( $cosof2sigma,
                                                                               2 ) ) ) );
            $s   = $ellipsoid->getMinorSemiAxis() * $A * ( $sigma - $deltaSigma );
            $s          = floor( $s * 1000 ) / 1000;

            return $s;
        }
    }

    /**
     * @param Point $point1 distance from this point
     * @param Point $point2 distance to this point
     * @param string $unit of measurement in which to return the result
     * @param null|int $formula formula to use, either Location::VINCENTY or Location::HAVERSINE. Defaults to Location::$defaultFormula
     *
     * @return float
     */
    public static function calculateDistance( Point $point1, Point $point2, $unit, $formula = null )
    {
        if ($formula === null) {
            $formula = self::$defaultFormula;
        }
        if ($formula === self::VINCENTY) {
            $mDistance = self::vincenty( $point1, $point2 );
            if ($unit === 'm') {
                return $mDistance;
            } else {

                return self::convert( $mDistance, 'm', $unit );
            }
        } else {
            $radDistance = self::haversine( $point1, $point2 );

            return $radDistance * self::getEllipsoid()->radius( $unit );
        }
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
    public static function convert( $distance, $from, $to )
    {

        $ellipsoid = self::getEllipsoid();

        $m = $distance / $ellipsoid->getMultiplier( $from );

        return $m * $ellipsoid->getMultiplier( $to );

    }

    public static function getBBoxByRadius( Point $point, $radius, $unit = 'km' )
    {
        $north = $point->getRelativePoint( $radius, 0, $unit );
        $south = $point->getRelativePoint( $radius, 180, $unit );

        $limits['n'] = $north->lat;
        $limits['s'] = $south->lat;

        $radDist = $radius / Location::getEllipsoid()->radius( $unit );
        //   $minLat  = deg2rad( $limits['s'] );
        //   $maxLat  = deg2rad( $limits['n'] );
        $radLon = $point->longitudeToRad();
        //if ($minLat > deg2rad(-90) && $maxLat < deg2rad(90)) {
        $deltaLon = asin( sin( $radDist ) / cos( $point->latitudeToRad() ) );
        $minLon   = $radLon - $deltaLon;
        if ($minLon < deg2rad( - 180 )) {
            $minLon += 2 * pi();
        }
        $maxLon = $radLon + $deltaLon;
        if ($maxLon > deg2rad( 180 )) {
            $maxLon -= 2 * pi();
        }
        //}

        $limits['w'] = rad2deg( $minLon );
        $limits['e'] = rad2deg( $maxLon );

        $nw      = new Point( $limits['n'], $limits['w'] );
        $ne      = new Point( $limits['n'], $limits['e'] );
        $sw      = new Point( $limits['s'], $limits['w'] );
        $se      = new Point( $limits['s'], $limits['e'] );
        $polygon = new Polygon( [ [ $nw, $ne, $se, $sw ] ] );

        return $polygon;
    }

    public static function getBBox( Geometry $geometry )
    {
        $maxLat = - 90;
        $minLat = 90;
        $maxLon = - 180;
        $minLon = 180;

        $points = $geometry->getPoints();

        /** @var Point $point */
        foreach ($points as $point) {
            $maxLat = ( $point->getLatitude() > $maxLat ) ? $point->getLatitude() : $maxLat;
            $minLat = ( $point->getLatitude() < $minLat ) ? $point->getLatitude() : $minLat;
            $maxLon = ( $point->getLongitude() > $maxLon ) ? $point->getLongitude() : $maxLon;
            $minLon = ( $point->getLongitude() < $minLon ) ? $point->getLongitude() : $minLon;
        }

        $nw = new Point( [ $minLon, $maxLat ] );
        $ne = new Point( [ $maxLon, $maxLat ] );
        $se = new Point( [ $maxLon, $minLat ] );
        $sw = new Point( [ $minLon, $minLat ] );

        return new Polygon( [ [ $nw, $ne, $se, $sw ] ] );
    }
} 