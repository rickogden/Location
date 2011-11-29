<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Line
 *
 * @author rick
 */
class Location_Line {
    protected $_start, $_end;
    
    public function __construct(Location_Point $start, Location_Point $end) {
        $this->_start = $start;
        $this->_end = $end;
    }
    
    /**
     * Get the length of the line
     * @return Location_Distance 
     */
    public function getLength() {
        return new Location_Distance($this->_start, $this->_end);
    }
    
    public function getMidPoint() {
        
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
