<?php

/**
 * A line (2 points only)
 *
 * @author Rick Ogden
 */

namespace Ricklab\Location;

require_once __DIR__ . '/Geometry.php';
require_once __DIR__ . '/Location.php';

class Line implements Geometry
{

    /**
     *
     * @var Point
     */
    protected $start, $end;

    /**
     * @param Point|array $start first point or array of points
     * @param Point|null $end 2nd point, ignored if $start is an array
     */
    public function __construct( $start, Point $end = null )
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Get the length of the line
     *
     * @param string $unit Unit of measurement
     * @param int $formula formula to use, should either be Location::HAVERSINE or Location::VINCENTY. Defaults to Location::$defaultFormula
     *
     * @return Float
     */
    public function getLength( $unit = 'km', $formula = null )
    {
        return Location::calculateDistance( $this->start, $this->end, $unit, $formula );
    }

    /**
     * Gets the mid-point of the line.
     * @return \Ricklab\Location\Point
     */
    public function getMidPoint()
    {
        return $this->start->getMidpoint( $this->end );
    }

    /**
     * Finds the initial bearing of the line
     * @return Number the bearing
     */
    public function getInitialBearing()
    {
        return $this->start->initialBearingTo( $this->end );
    }


    protected function _latDiff()
    {
        return $this->end->getLatitude() - $this->start->getLatitude();
    }

    protected function _lonDiff()
    {
        return deg2rad( $this->end->getLongitude() - $this->start->getLongitude() );
    }

    public function jsonSerialize()
    {
        return array(
            'type'        => 'LineString',
            'coordinates' => array(
                array( $this->start->getLongitude(), $this->start->getLatitude() ),
                array( $this->end->getLongitude(), $this->end->getLatitude() )
            )
        );
    }

    public function toSql()
    {
        return 'LineString(' . (string) $this->start . ', ' . (string) $this->end . ')';
    }

}
