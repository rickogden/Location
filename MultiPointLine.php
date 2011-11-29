<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MultiPointLine
 *
 * @author rick
 */
class Location_MultiPointLine implements SeekableIterator {
    protected $_points = array(), $_position = 0;
    
    public function __construct(array $points) {
        $this->_points = $points;
    }
    
    public function getLength($unit = 'km') {
        $distance = 0;
        for($i = 1; $i < count($this->_points); $i++) {
            $distance += $this->_points[$i-1]->distanceTo($this->_points[$i])->to($unit);
        }
        return $distance;
    }
    
    public function toArray() {
        return $this->_points;
    }
    
    public function seek($position) {
        $this->_position = $position;
        
        if(!$this->valid()) {
            throw new OutOfBoundsException('Item does not exist');
        }
    }
    
    public function current() {
        return $this->_points[$this->_position];
    }
    
    public function key() {
        return $this->_position;
    }
    
    public function next() {
        $this->_position++;
    }
    
    public function rewind() {
        $this->_position = 0;
    }
    
    public function valid() {
        return isset($this->_points[$this->_position]);
    }
    
    public function getPartial($start, $end) {
        if(($end - $start) == 1) {
            $line = new Location_Line($this->_points[$start], $this->_points[$end]);
        } else {
            $a = array_slice($this->_points, $start, $end - $start);
            $line = new Location_MultiPointLine($a);
        }
        
        return $line;
    }
    
    public function countPoints() {
        return count($this->_points);
    }
    
    
}