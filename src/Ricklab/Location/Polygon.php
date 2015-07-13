<?php

namespace Ricklab\Location;

require_once __DIR__ . '/MultiPointLine.php';

class Polygon extends MultiPointLine
{

    /**
     *
     * @param Array $points
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
        foreach ($this->points as $i => $point) {
            if ($i > 0) {
                $text .= ', ';
            }
            $text .= $point;
        }
        $text .= '))';

        return $text;
    }

    public function jsonSerialize()
    {
        $geo = parent::jsonSerialize();
        $geo['type'] = 'Polygon';
        $geo['coordinates'] = [$geo['coordinates']];

        return $geo;
    }

    public function getPerimeter( $unit = 'km', $formula = null )
    {
        return $this->getLength( $unit, $formula );
    }

}