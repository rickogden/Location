<?php
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 17:18
 */

namespace Ricklab\Location\Geometry;

/**
 * Class GeometryCollection
 * @package Ricklab\Location\Geometry
 *
 * A collection of any combination of geometries.
 */
class GeometryCollection implements GeometryInterface, GeometryCollectionInterface
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
        return 'GEOMETRYCOLLECTION' . $this;
    }

    /**
     * All the geometries contained as an array.
     *
     * @return GeometryInterface[]
     */
    public function toArray()
    {
        return $this->geometries;
    }

    /**
     * @return Point[] gets all the points in a geometry. Note, order is not necessarily representative.
     */
    public function getPoints()
    {
        $points = [];
        foreach ($this->geometries as $geometry) {

            $geomPoints = $geometry->getPoints();
            $points += $geomPoints;

        }

        return $points;
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
        $json['type'] = 'GeometryCollection';
        foreach ($this->geometries as $geometry) {
            $json['geometries'][] = $geometry->jsonSerialize();
        }

        return $json;
    }

    public function __toString()
    {
        $collection = [];
        foreach ($this->geometries as $geometry) {

            $collection[] = $geometry->toWkt();

        }

        $string = '(' . implode(',', $collection) . ')';

        return $string;
    }

    /**
     * All the geometries in the collection
     * @return array|GeometryInterface[]
     */
    public function getGeometries()
    {
        return $this->geometries;
    }

    /**
     * Adds a geometry to the collection
     *
     * @param GeometryInterface $geometry
     *
     * @return $this
     */
    public function addGeometry(GeometryInterface $geometry)
    {
        $this->geometries[] = $geometry;

        return $this;
    }

    /**
     * Removes a geometry from the collection
     *
     * @param GeometryInterface $geometry
     *
     * @return $this
     */
    public function removeGeometry(GeometryInterface $geometry)
    {

        foreach ($this->geometries as $index => $geom) {
            if ($geom === $geometry) {
                unset( $this->geometries[$index] );
            }
        }

        return $this;
    }
}