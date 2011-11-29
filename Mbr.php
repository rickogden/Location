<?php

class Location_Mbr {

    protected $_point, $_radius, $_unit, $_polygon;
    protected $_limits = array('n' => null, 's' => null, 'e' => null, 'w' => null);

    public function __construct(Location_Point $point, $radius, $unit = 'km') {
        $this->_point = $point;
        $this->_radius = $radius;
        $this->_unit = $unit;
        $this->_setLimits();
    }

    protected function _setLimits() {
        /* $this->_limits['n'] =  $this->_point->getRelativePoint($this->_radius, 0, $this->_unit)->getLatitude();
          $this->_limits['s']= $this->_point->getRelativePoint($this->_radius, 180, $this->_unit)->getLatitude();
          $radius = Location_Earth::radius($this->_unit);
          $latt = asin(sin($this->_point->getLatitude())/  cos($radius));
          $tlon = acos((cos($radius)-sin($latt)*sin($this->_point->getLatitude()))/(cos($latt)*  cos($this->_point->getLatitude()))); */



        $north = $this->_point->getRelativePoint($this->_radius, '0', $this->_unit);
        $south = $this->_point->getRelativePoint($this->_radius, '180', $this->_unit);

        $this->_limits['n'] = $north->lat;
        $this->_limits['s'] = $south->lat;

        $radDist = $this->_radius / Location_Earth::radius($this->_unit);
        $minLat = deg2rad($this->_limits['s']);
        $maxLat = deg2rad($this->_limits['n']);
        $radLon = $this->_point->longitudeToRad();
        //if ($minLat > deg2rad(-90) && $maxLat < deg2rad(90)) {
        $deltaLon = asin(sin($radDist) / cos($this->_point->latitudeToRad()));
        $minLon = $radLon - $deltaLon;
        if ($minLon < deg2rad(-180)) {
            $minLon += 2 * pi();
        }
        $maxLon = $radLon + $deltaLon;
        if ($maxLon > deg2rad(180)){
            $maxLon -= 2 * pi();
        }
        //}
        //
        $this->_limits['w'] = rad2deg($minLon);
        $this->_limits['e'] = rad2deg($maxLon);
    }

    public function getLocation() {
        return $this->_point;
    }

    /**
     *
     * @return Location_Polygon 
     */
    public function toPolygon() {
        if ($this->_polygon === null) {
            $nw = new Location_Point($this->_limits['n'], $this->_limits['w']);
            $ne = new Location_Point($this->_limits['n'], $this->_limits['e']);
            $sw = new Location_Point($this->_limits['s'], $this->_limits['w']);
            $se = new Location_Point($this->_limits['s'], $this->_limits['e']);
            $this->_polygon = new Location_Polygon(array($nw, $ne, $se, $sw));
        }

        return $this->_polygon;
    }

    public function __get($offset) {
        return $this->_limits[$offset];
    }

}
