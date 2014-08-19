<?php

namespace Ricklab\Location;

require_once __DIR__ . '/Earth.php';
require_once __DIR__ . '/Polygon.php';
require_once __DIR__ . '/Geometry.php';

class Mbr implements Geometry
{

    /**
     *
     * @var Point
     */
    protected $point;
    protected $radius, $unit;

    /**
     *
     * @var Polygon
     */
    protected $polygon;

    /**
     *
     * @var Point[string]
     */
    protected $limits = array('n' => null, 's' => null, 'e' => null, 'w' => null);

    public function __construct(Point $point, $radius, $unit = 'km')
    {
        $this->point = $point;
        $this->radius = $radius;
        $this->unit = $unit;
        $this->_setLimits();
    }

    protected function _setLimits()
    {

        $north = $this->point->getRelativePoint($this->radius, 0, $this->unit);
        $south = $this->point->getRelativePoint($this->radius, 180, $this->unit);

        $this->limits['n'] = $north->lat;
        $this->limits['s'] = $south->lat;

        $radDist = $this->radius / Location::getPlanet()->radius($this->unit);
        $minLat = deg2rad($this->limits['s']);
        $maxLat = deg2rad($this->limits['n']);
        $radLon = $this->point->longitudeToRad();
        //if ($minLat > deg2rad(-90) && $maxLat < deg2rad(90)) {
        $deltaLon = asin(sin($radDist) / cos($this->point->latitudeToRad()));
        $minLon = $radLon - $deltaLon;
        if ($minLon < deg2rad(-180)) {
            $minLon += 2 * pi();
        }
        $maxLon = $radLon + $deltaLon;
        if ($maxLon > deg2rad(180)) {
            $maxLon -= 2 * pi();
        }
        //}

        $this->limits['w'] = rad2deg($minLon);
        $this->limits['e'] = rad2deg($maxLon);
    }

    public function getLocation()
    {
        return $this->point;
    }

    /**
     *
     * @return Polygon
     */
    public function toPolygon()
    {
        if ($this->polygon === null) {
            $nw = new Point($this->limits['n'], $this->limits['w']);
            $ne = new Point($this->limits['n'], $this->limits['e']);
            $sw = new Point($this->limits['s'], $this->limits['w']);
            $se = new Point($this->limits['s'], $this->limits['e']);
            $this->polygon = new Polygon(array($nw, $ne, $se, $sw));
        }

        return $this->polygon;
    }

    public function __get($offset)
    {
        return $this->limits[$offset];
    }

    public function jsonSerialize()
    {

        return $this->toPolygon()->jsonSerialize();
    }

    public function toSql()
    {
        return $this->toPolygon()->toSql();
    }

}
