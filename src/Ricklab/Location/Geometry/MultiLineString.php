<?php
/**
 * Author: rick
 * Date: 16/07/15
 * Time: 16:34
 */

namespace Ricklab\Location\Geometry;


class MultiLineString implements GeometryInterface, GeometryCollectionInterface
{

    /**
     * @var LineString[]
     */
    protected $geometries = [];

    public function __construct( array $lineStrings )
    {
        foreach ($lineStrings as $lineString) {
            if ( ! $lineString instanceof LineString) {
                $lineString = new LineString($lineString);
            }

            $this->geometries[] = $lineString;
        }
    }

    /**
     * @return string the Well-Known Text representation of the geometry
     */
    public function toWkt()
    {
        return 'MULTILINESTRING' . (string) $this;
    }

    /**
     * @return Point[] gets all the points in a geometry. Note, order is not necessarily representative.
     */
    public function getPoints()
    {
        $points = [ ];
        foreach ($this->geometries as $line) {
            $linePoints = $line->getPoints();
            $points += $linePoints;
        }

        return $points;
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
        $geo = [
            'type'        => 'MultiLineString',
            'coordinates' => $this->toArray()
        ];

        return $geo;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $return = [];
        foreach ($this->geometries as $line) {
            $return[] = $line->toArray();
        }

        return $return;
    }

    /**
     * @return LineString[] an array of the LineStrings
     */
    public function getGeometries()
    {
        return $this->geometries;
    }

    /**
     * Adds a new LineString to the collection.
     *
     * @param LineString $lineString
     *
     * @return $this
     */
    public function addGeometry(LineString $lineString)
    {
        $this->geometries[] = $lineString;

        return $this;
    }

    /**
     * Removes a LineString from the collection
     *
     * @param LineString $lineString
     *
     * @return $this
     */
    public function removeGeometry(LineString $lineString)
    {
        foreach ($this->geometries as $index => $geom) {
            if ($lineString === $geom) {
                unset( $this->geometries[$index] );
            }
        }

        return $this;
    }

    public function __toString()
    {
        return '(' . implode(',', $this->geometries) . ')';
    }

}