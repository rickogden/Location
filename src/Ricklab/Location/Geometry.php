<?php


namespace Ricklab\Location;


interface Geometry extends \JsonSerializable
{
    /**
     * @return string the Well-Known Text representation of the geometry
     */
    public function toWkt();

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return Point[] gets all the points in a geometry. Note, order is not necessarily representative.
     */
    public function getPoints();

} 