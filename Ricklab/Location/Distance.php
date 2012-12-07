<?php

/**
 * Distance object for calculating and displaying distances between 2 Point objects.
 *
 * @author Rick Ogden
 */
namespace Ricklab\Location;

class Distance {

    //put your code here
    protected $_firstLocation, $_secondLocation, $_distanceLat, $_distanceLong, $_distance;
    
    /**
     *
     * @param Point $firstLocation
     * @param Point $secondLocation 
     */
    public function __construct(Point $firstLocation, Point $secondLocation) {
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
        $radius = Earth::radius($unit);
        
        } catch(InvalidArgumentException $e) {
            return $e->getMessage();
        }
        
        return $this->_distance * $radius;
    }

}