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

    public static function convert( $distance, $from, $to )
    {

        $planet = self::getPlanet();

        $km = $distance / $planet->getMultiplier( $from );

        return $km * $planet->getMultiplier( $to );

    }
} 