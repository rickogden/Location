<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Point
 *
 * @author rick
 */

namespace Ricklab\Location;

require_once __DIR__ . '/Geometry.php';

class Point implements Geometry
{

    protected $longitude, $latitude;

    /**
     * Create a new Point from Longitude and latitude.
     *
     * Usage: new Point(latitude, longitude);
     * or new Point([longitude, latitude]);
     *
     * @param Number|Array $lat Latitude coordinates or a coordinates array in the order of [longitude, latitude]
     * @param Number $long Longitude coordinates
     */
    public function __construct( $lat, $long = null )
    {
        if ($long === null) {
            if (is_array( $lat )) {
                $long = $lat[0];
                $lat = $lat[1];
            } else {
                throw new \InvalidArgumentException( 'Arguments must be an array or two numbers.' );
            }
        }
        if ( ! is_numeric( $long ) || $long > 180 || $long < - 180) {
            throw new \InvalidArgumentException( 'longitude must be a valid number between -180 and 180.' );
        }

        if ( ! is_numeric( $lat ) || $lat > 90 || $lat < - 90) {
            throw new \InvalidArgumentException( 'latitude must be a valid number between -90 and 90.' );
        }

        $this->longitude = (float) $long;
        $this->latitude  = (float) $lat;
    }

    /**
     * Get the latitude in Rads
     * @return Number Latitude in Rads
     */
    public function latitudeToRad()
    {
        return deg2rad( $this->latitude );
    }

    /**
     * Get the longitude in Rads
     * @return Number Longitude in Rads
     */
    public function longitudeToRad()
    {
        return deg2rad( $this->longitude );
    }

    /**
     *
     * @return String
     */
    public function __toString()
    {
        return $this->longitude . ' ' . $this->latitude;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Find distance to another point
     *
     * @param Point $point2
     * @param string $unit
     * @param int $formula formula to use, should either be Location::HAVERSINE or Location::VINCENTY. Defaults to Location::$defaultFormula
     *
     * @return float the distance
     */
    public function distanceTo( Point $point2, $unit = 'km', $formula = null )
    {
        return Location::calculateDistance( $this, $point2, $unit, $formula );
    }

    public function __get( $request )
    {
        $request = strtolower( $request );
        if (in_array( $request, array( 'x', 'lon', 'long', 'longitude' ) )) {
            return $this->longitude;
        } elseif (in_array( $request, array( 'y', 'lat', 'latitude' ) )) {
            return $this->latitude;
        } else {
            throw new \InvalidArgumentException( 'Unexpected value for retrieval' );
        }
    }

    /**
     * Find a location a distance and bearing from this one
     *
     * @param Number $distance distance to other point
     * @param Number $bearing initial bearing to other point
     * @param String $unit The unit the distance is in
     *
     * @return Point
     */
    public function getRelativePoint( $distance, $bearing, $unit = 'km' )
    {
        $rad = Location::getEllipsoid()->radius( $unit );
        $lat1    = $this->latitudeToRad();
        $lon1    = $this->longitudeToRad();
        $bearing = deg2rad( $bearing );

        $lat2 = sin( $lat1 ) * cos( $distance / $rad ) +
                cos( $lat1 ) * sin( $distance / $rad ) * cos( $bearing );
        $lat2 = asin( $lat2 );

        $lon2y = sin( $bearing ) * sin( $distance / $rad ) * cos( $lat1 );
        $lon2x = cos( $distance / $rad ) - sin( $lat1 ) * sin( $lat2 );
        $lon2  = $lon1 + atan2( $lon2y, $lon2x );

        return new self( rad2deg( $lat2 ), rad2deg( $lon2 ) );
    }

    /**
     * Get the bearing from this Point to another.
     *
     * @param Point $point2
     *
     * @return float bearing
     */
    public function initialBearingTo( Point $point2 )
    {

        if (function_exists( 'initial_bearing' ) && Location::$useSpatialExtension) {
            return initial_bearing( $this->jsonSerialize(), $point2->jsonSerialize() );
        } else {
            $y      = sin(
                          deg2rad( $point2->getLongitude() - $this->getLongitude() )
                      ) * cos( $point2->latitudeToRad() );
            $x      = cos( $this->latitudeToRad() )
                      * sin( $point2->latitudeToRad() ) - sin(
                                                              $this->latitudeToRad()
                                                          ) * cos( $point2->latitudeToRad() ) *
                                                          cos(
                                                              deg2rad( $point2->getLongitude() - $this->getLongitude() ) );
            $result = atan2( $y, $x );

            return fmod( rad2deg( $result ) + 360, 360 );
        }
    }


    public function getMidpoint( Point $point )
    {
        $bx   = cos( $point->latitudeToRad() ) * cos( deg2rad( $point->getLongitude() - $this->getLongitude() ) );
        $by   = cos( $point->latitudeToRad() ) * sin( deg2rad( $point->getLongitude() - $this->getLongitude() ) );
        $mLat = atan2(
            sin( $this->latitudeToRad() ) + sin( $point->latitudeToRad() ),
            sqrt( pow( cos( $this->latitudeToRad() ) + $bx, 2 ) + pow( $by, 2 ) )
        );

        $mLon = $this->longitudeToRad() + atan2( $by, cos( $this->latitudeToRad() ) + $bx );

        return new self( rad2deg( $mLat ), rad2deg( $mLon ) );
    }

    /**
     * Create a line between this point and another point
     *
     * @param Point $point
     *
     * @return Line
     */
    public function lineTo( Point $point )
    {
        return new Line( $this, $point );
    }

    /**
     * @param $distance
     * @param string $unit
     *
     * @return Polygon
     * @deprecated
     */
    public function getMbr( $distance, $unit = 'km' )
    {
        return $this->getBBoxByRadius( $distance, $unit );
    }

    /**
     * @param $radius
     * @param string $unit
     *
     * @return Polygon
     */
    public function getBBoxByRadius( $radius, $unit = 'km' )
    {
        return Location::getBBoxByRadius( $this, $radius, $unit );
    }

    public function toWkt()
    {
        return 'POINT(' . (string) $this . ')';
    }

    /**
     * @return float[]
     */
    public function getCoordinates()
    {
        return [ $this->longitude, $this->latitude ];
    }

    /**
     * A GeoJSON representation of the class.
     * @return array a geoJSON representation
     */
    public function jsonSerialize()
    {
        return array(
            'type' => 'Point',
            'coordinates' => $this->toArray()
        );
    }

    public function toArray()
    {
        return [ $this->longitude, $this->latitude ];
    }

    public function getPoints()
    {
        return [ $this ];
    }

}