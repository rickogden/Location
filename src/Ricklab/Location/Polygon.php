<?php

namespace Ricklab\Location;

class Polygon extends MultiPointLine
{

    /**
     *
     * @param Point[int] $points 
     */
    public function __construct($points)
    {
        if ($points instanceof MultiPointLine) {
            $points = $points->toArray();
        }

        if (end($points) != $points[0]) {
            $points[] = $points[0];
        }
        parent::__construct($points);
    }

    public function toSql()
    {
        $text = 'POLYGON((';
        foreach ($this->_points as $i => $point) {
            if ($i > 0)
                $text .= ', ';
            $text .= $point;
        }
        $text .= '))';

        return $text;
    }

    public function jsonSerialize()
    {
        $geo = parent::jsonSerialize();
        $geo['type'] = 'Polygon';

        return $geo;
    }

}