<?php
/**
 * A line consisting of more than 2 points
 *
 * @author rick
 */

namespace Ricklab\Location;

require_once __DIR__ . '/Geometry.php';

class MultiPointLine extends Geometry implements \SeekableIterator
{

    /**
     *
     * @var Point[int] 
     */
    protected $points = array();
    protected $position = 0;

    /**
     *
     * @param Array $points
     */
    public function __construct(array $points)
    {
        foreach ($points as $point) {
            if (!$point instanceof Point) {
                throw new \InvalidArgumentException('Array must consist of Point objects');
            }
        }
        $this->points = $points;
    }

    public function getLength($unit = 'km')
    {
        $distance = 0;
        for ($i = 1; $i < count($this->points); $i++) {
            $distance += $this->points[$i - 1]->distanceTo($this->points[$i], $unit);
        }
        return $distance;
    }

    public function toArray()
    {
        return $this->points;
    }

    public function seek($position)
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException('Item does not exist');
        }
    }

    public function current()
    {
        return $this->points[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return isset($this->points[$this->position]);
    }

    public function getPartial($start, $end)
    {
        if (($end - $start) == 1) {
            $line = new Line($this->points[$start], $this->points[$end]);
        } else {
            $a = array_slice($this->points, $start, $end - $start);
            $line = new MultiPointLine($a);
        }

        return $line;
    }

    public function countPoints()
    {
        return count($this->points);
    }
    
    public function jsonSerialize()
    {
        $coordinates = array();
        /** @var Point $point */
        foreach ($this->points as $point) {
            $coordinates[] = array($point->getLongitude(), $point->getLatitude());
        }
        
        return array('type' => 'LineString', 'coordinates' => $coordinates);
    }

    public function toSql() {
        $retVal = 'LineString(';
        $pointArray = array();
        /** @var Point $point */
        foreach ($this->points as $point) {
            $pointArray[] = (string) $point;
        }

        $retVal .= implode(', ', $pointArray);
        $retVal .= ')';

        return $retVal;
    }

}