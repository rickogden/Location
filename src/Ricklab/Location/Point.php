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
        return $this->latitude . ' ' . $this->longitude;
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
        $rad     = Location::getPlanet()->radius( $unit );
        $lat1    = $this->latitudeToRad();
        $lon1    = $this->longitudeToRad();
        $bearing = deg2rad( $bearing );

        $lat2 = sin( $lat1 ) * cos( $distance / $rad ) +
                cos( $lat1 ) * sin( $distance / $rad ) * cos( $bearing );
        $lat2 = asin( $lat2 );

        $lon2y = sin( $bearing ) * sin( $distance / $rad ) * cos( $lat1 );
        $lon2x = cos( $distance / $rad ) - sin( $lat1 ) * sin( $lat2 );
        $lon2  = $lon1 + atan2( $lon2y, $lon2x );

        return new Point( rad2deg( $lat2 ), rad2deg( $lon2 ) );
    }

    /**
     *
     * @param Point $point2
     *
     * @return Number
     * @deprecated use initialBearingTo instead
     */
    public function bearingTo( Point $point2 )
    {
        return $this->lineTo( $point2 )->getBearing();
    }

    public function initialBearingTo( Point $point2 )
    {
        return $this->lineTo( $point2 )->getInitialBearing();
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

    public function getMbr( $distance, $unit = 'km' )
    {
        return new Mbr( $this, $distance, $unit );
    }

    public function toSql()
    {
        return 'POINT(' . (string) $this . ')';
    }

    /**
     * A GeoJSON representation of the class.
     * @return array a geoJSON representation
     */
    public function jsonSerialize()
    {
        return array(
            'type' => 'Point',
            'coordinates'
                   => array( $this->longitude, $this->latitude )
        );
    }

}