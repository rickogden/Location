<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * A line (2 points only)
 *
 * @author Rick Ogden
 */
namespace Ricklab\Location;

class Line {
    
    /**
     *
     * @var Point 
     */
    protected $_start, $_end;
    
    public function __construct(Point $start, Point $end) {
        $this->_start = $start;
        $this->_end = $end;
    }
    
    /**
     * Get the length of the line
     * @return Distance 
     */
    public function getLength() {
        return new Distance($this->_start, $this->_end);
    }
    
    public function getMidPoint() {
        //TODO
    }
    
    /**
     * Find the bearing of the line
     * @return Number the bearing
     */
    public function getBearing() {
        $y = sin($this->_lonDiff()) * cos($this->_end->latitudeToRad());
        $x = cos($this->_start->latitudeToRad()) * sin($this->_end->latitudeToRad())
                - sin($this->_start->latitudeToRad()) * cos($this->_end->latitudeToRad())
                        * cos($this->_lonDiff());
        $result = atan2($y, $x);
        
        return fmod(rad2deg($result) + 360, 360);
    }
    
    protected function _latDiff() {
        return $this->_end->latitude - $this->_start->latitude;
    }
    
    protected function _lonDiff() {
        return deg2rad($this->_end->getLongitude() - $this->_start->getLongitude());
    }
}
