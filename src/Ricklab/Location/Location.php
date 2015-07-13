<?php
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 09:58
 */

namespace Ricklab\Location;


class Location
{

    /**
     * @var Planet
     */
    protected static $planet;

    /**
     * @var bool Set to false if you have the pecl geospatial extension installed but do not want to use it
     */
    public static $useSpatialExtension = true;

    /**
     * Set the planet to perform the calculations on
     *
     * @param Planet $planet
     */
    public static function setPlanet( Planet $planet )
    {

        self::$planet = $planet;

    }

    /**
     * @return Earth|Planet the planet in use (generally Earth)
     */
    public static function getPlanet()
    {
        if (self::$planet === null) {
            self::$planet = new Earth;
        }

        return self::$planet;
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
                if (count( $points ) > 2) {
                    $result = new MultiPointLine( $points );
                } elseif (count( $points ) === 2) {
                    $result = new Line( $points[0], $points[1] );
                } else {
                    throw new \ErrorException( 'cannot parse as Line' );
                }
                break;
            case 'Polygon':
                $points = array();
                foreach ($coordinates[0] as $coordinate) {
                    if (is_array( $coordinate )) {
                        $points[] = new Point( $coordinate );
                    }

                    $result = new Polygon( $points );
                }

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
        if (function_exists( 'vincenty' ) && self::$useSpatialExtension && self::$planet instanceof Earth) {

            $from = $point1->jsonSerialize();
            $to   = $point2->jsonSerialize();

            $distance = vincenty( $from, $to );

            return $distance;

        } else {
            $planet = self::$planet;

            $U1            = atan( ( 1.0 - $planet->getFlattening() ) * tan( $point1->latitudeToRad() ) );
            $U2            = atan( ( 1.0 - $planet->getFlattening() ) * tan( $point2->latitudeToRad() ) );
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
                $C      = $planet->getFlattening() / 16 * $cos2Alpha * ( 4 + $planet->getFlattening() * ( 4 - 3 * $cos2Alpha ) );
                $lambdaP = $lambda;
                $lambda = $L + ( 1 - $C ) * $planet->getFlattening() * $sinAlpha *
                               ( $sigma + $C * $sinSigma * ( $cosof2sigma + $C * $cosSigma * ( - 1 + 2 * pow( $cosof2sigma,
                                               2 ) ) ) );

            } while (abs( $lambda - $lambdaP ) > 1e-12 && -- $looplimit > 0);

            $uSq        = $cos2Alpha * ( pow( $planet->getMajorSemiAxis(), 2 ) - pow( $planet->getMinorSemiAxis(),
                        2 ) ) / pow( $planet->getMinorSemiAxis(), 2 );
            $A          = 1 + $uSq / 16384 * ( 4096 + $uSq * ( - 768 + $uSq * ( 320 - 175 * $uSq ) ) );
            $B          = $uSq / 1024 * ( 256 + $uSq * ( - 128 + $uSq * ( 74 - 47 * $uSq ) ) );
            $deltaSigma = $B * $sinSigma * ( $cosof2sigma + $B / 4 * ( $cosSigma * ( - 1 + 2 * pow( $cosof2sigma,
                                2 ) ) -
                                                                       $B / 6 * $cosof2sigma * ( - 3 + 4 * pow( $sinSigma,
                                                                               2 ) ) * ( - 3 + 4 * pow( $cosof2sigma,
                                                                               2 ) ) ) );
            $s          = $planet->getMinorSemiAxis() * $A * ( $sigma - $deltaSigma );
            $s          = floor( $s * 1000 ) / 1000;

            return $s;
        }
    }

    public static function convert( $distance, $from, $to )
    {

        $planet = self::getPlanet();

        $km = $distance / $planet->getMultiplier( $from );

        return $km * $planet->getMultiplier( $to );

    }
} 