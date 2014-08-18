<?php

/**
 * A line (2 points only)
 *
 * @author Rick Ogden
 */

namespace Ricklab\Location;

require_once __DIR__ . '/Geometry.php';
require_once __DIR__.'/Distance.php';

class Line extends Geometry
{

    /**
     *
     * @var Point 
     */
    protected $start, $end;

    public function __construct(Point $start, Point $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Get the length of the line
     * @param String $unit Unit of measurement
     * @return Float
     */
    public function getLength($unit = 'km')
    {
        return $this->start->distanceTo($this->end, $unit);
    }

    /**
     * Gets the mid-point of the line.
     * @return \Ricklab\Location\Point
     */
    public function getMidPoint()
    {
        $bx = cos($this->end->latitudeToRad()) * cos($this->_lonDiff());
        $by = cos($this->end->latitudeToRad()) * sin($this->_lonDiff());
        $mLat = atan2(
            sin($this->start->latitudeToRad()) + sin($this->end->latitudeToRad()),
            sqrt(pow(cos($this->start->latitudeToRad()) + $bx, 2) + pow($by, 2))
        );

        $mLon = $this->start->longitudeToRad() + atan2($by, cos($this->start->latitudeToRad()) + $bx);

        return new Point(rad2deg($mLat), rad2deg($mLon));
    }

    /**
     * Finds the initial bearing of the line
     * @return Number the bearing
     */
    public function getBearing()
    {
        return $this->getInitialBearing();
    }

    /**
     * Finds the initial bearing of the line
     * @return Number the bearing
     */
    public function getInitialBearing()
    {
        if (function_exists('initial_bearing') && Location::$usePeclExtension) {
            return initial_bearing($this->start->jsonSerialize(), $this->end->jsonSerialize());
        } else {
            $y = sin($this->_lonDiff()) * cos($this->end->latitudeToRad());
            $x = cos($this->start->latitudeToRad()) * sin($this->end->latitudeToRad()) - sin(
                    $this->start->latitudeToRad()
                ) * cos($this->end->latitudeToRad()) * cos($this->_lonDiff());
            $result = atan2($y, $x);

            return fmod(rad2deg($result) + 360, 360);
        }
    }


    protected function _latDiff()
    {
        return $this->end->latitude - $this->start->latitude;
    }

    protected function _lonDiff()
    {
        return deg2rad($this->end->getLongitude() - $this->start->getLongitude());
    }

    public function jsonSerialize()
    {
        return array('type' => 'LineString', 'coordinates' => array(
            array($this->start->getLongitude(), $this->start->getLatitude()),
            array($this->end->getLongitude(), $this->end->getLatitude())
        ));
    }

    public function toSql()
    {
        return 'LineString(' . (string)$this->start . ', ' . (string)$this->end . ')';
    }

}
