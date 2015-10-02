<?php
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 17:18
 */

namespace Ricklab\Location\Geometry;


class GeometryCollection implements GeometryInterface
{
    /**
     * @var GeometryInterface[]
     */
    protected $geometries = [ ];

    /**
     * GeometryCollection constructor.
     *
     * @param GeometryInterface[] $geometries
     */
    public function __construct( array $geometries )
    {
        foreach ($geometries as $geometry) {
            if ( ! $geometry instanceof GeometryInterface) {
                throw new \InvalidArgumentException( 'Array must contain geometries only' );
            }
        }
        $this->geometries = $geometries;
    }

    /**
     * @return string the Well-Known Text representation of the geometry
     */
    public function toWkt()
    {
        // TODO: Implement toWkt() method.
    }

    /**
     * @return array
     */
    public function toArray()
    {
        // TODO: Implement toArray() method.
    }

    /**
     * @return Point[] gets all the points in a geometry. Note, order is not necessarily representative.
     */
    public function getPoints()
    {
        // TODO: Implement getPoints() method.
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializabl2e.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }

    public function __toString()
    {
        $string = '(';
        foreach ($this->geometries as $geometry) {


        }
        $string .= ')';

        return $string;
    }


}