<?php
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:24
 */

namespace Ricklab\Location\Geometry;


use Ricklab\Location\Location;

class MultiPoint implements GeometryInterface, GeometryCollectionInterface, \SeekableIterator, \ArrayAccess
{

    /**
     * @var Point[]
     */
    protected $geometries;

    protected $position = 0;

    function __construct(array $points)
    {
        foreach ($points as $point) {

            $this[] = $point;
        }

    }

    /**
     * @return string the Well-Known Text representation of the geometry
     */
    public function toWkt()
    {
        $retVal = 'MULTIPOINT' . $this;

        return $retVal;
    }

    /**
     * @return Point[]
     */
    public function getGeometries()
    {
        return $this->getPoints();
    }

    /**
     * @return Point[] gets all the points in a geometry. Note, order is not necessarily representative.
     */
    public function getPoints()
    {
        return $this->geometries;
    }

    /**
     * @param Point $point
     *
     * @return $this
     */
    public function addGeometry(Point $point)
    {
        $this->geometries[] = $point;

        return $this;
    }

    /**
     * @param Point $point
     *
     * @return $this
     */
    public function removeGeometry(Point $point)
    {
        foreach ($this->geometries as $index => $geom) {
            if ($point === $geom) {
                unset( $this->geometries[$index] );
            }
        }

        return $this;
    }

    public function __toString()
    {
        $return = '(' . implode(', ', $this->geometries) . ')';

        return $return;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        $coordinates = $this->toArray();

        return ['type' => 'MultiPoint', 'coordinates' => $coordinates];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $return = [];
        foreach ($this->geometries as $point) {
            $return[] = $point->toArray();
        }

        return $return;
    }

    public function seek($position)
    {
        $this->position = $position;

        if ( ! $this->valid()) {
            throw new \OutOfBoundsException('Item does not exist');
        }
    }

    public function valid()
    {
        return isset( $this->geometries[$this->position] );
    }

    public function current()
    {
        return $this->geometries[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position ++;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function offsetExists($offset)
    {
        return isset( $this->geometries[$offset] );
    }


    public function offsetGet($offset)
    {
        return $this->geometries[$offset];
    }


    public function offsetSet($offset, $value)
    {
        if ( ! $value instanceof Point) {
            $value = new Point($value);
        }

        if (is_integer($offset)) {
            $this->geometries[$offset] = $value;
        } elseif ($offset === null) {
            $this->geometries[] = $value;
        } else {
            throw new \OutOfBoundsException('Key must be numeric.');
        }
    }

    public function offsetUnset(
        $offset
    ) {
        unset( $this->geometries[$offset] );
    }

    public function getBBox()
    {
        return Location::getBBox($this);
    }

}