<?php

class Location_Polygon extends Location_MultiPointLine {
    
    public function __construct($points) {
        if($points instanceof Location_MultiPointLine) {
            $points = $points->toArray();
        }
        
        if(end($points) == $points[0]) {
            array_pop($points);
        }
        parent::__construct($points);
    }
    
    public function toSql() {
        $text = 'POLYGON((';
        foreach($this->_points as $i => $point) {
            if($i > 0)$text .= ', ';
            $text .= $point;
        }
        $text .= '))';
    }
}