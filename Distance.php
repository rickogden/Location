<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Distance
 *
 * @author rick
 */
class Location_Distance {

    //put your code here
    protected $_firstLocation, $_secondLocation, $_distanceLat, $_distanceLong, $_distance;

    public function __construct(Location_Point $firstLocation, Location_Point $secondLocation) {
        $this->_firstLocation = $firstLocation;
        $this->_secondLocation = $secondLocation;
        $this->_distanceLat = $firstLocation->latitudeToRad() - $secondLocation->latitudeToRad();
        $this->_distanceLong = $firstLocation->longitudeToRad() - $secondLocation->longitudeToRad();
        $this->_distance = $this->_trigCalc();
    }
    
    protected function _trigCalc() {
        $distance = sin($this->_distanceLat/2) * sin($this->_distanceLat/2) +
            cos($this->_firstLocation->latitudeToRad()) * cos($this->_secondLocation->latitudeToRad()) * 
            sin($this->_distanceLong/2) * sin($this->_distanceLong/2);
        $distance = 2 * asin(sqrt($distance));
        
        return $distance;
    }
    
    public function getBearing() {
        $y = sin($this->_distanceLong) * cos($this->_secondLocation->latitudeToRad());
        $x = cos($this->_firstLocation->latitudeToRad())*sin($this->_secondLocation->latitudeToRad()) -
                sin($this->_firstLocation->latitudeToRad()) * cos($this->_distanceLong);
        $bearing = atan2($y, $x);
        return rad2deg($bearing);
    }

    public function toMiles() {
        return $this->to('miles');
    }

    public function toKm() {
        return $this->to('km');
    }

    public function to($unit) {
        try {
        $radius = Location_Earth::radius($unit);
        
        } catch(InvalidArgumentException $e) {
            return $e->getMessage();
        }
        
        return $this->_distance * $radius;
    }

}