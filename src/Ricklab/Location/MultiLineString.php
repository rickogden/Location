<?php
/**
 * Author: rick
 * Date: 16/07/15
 * Time: 16:34
 */

namespace Ricklab\Location;


class MultiLineString implements Geometry
{

    /**
     * @var LineString[]
     */
    protected $lineStrings = [ ];

    public function __construct( array $lineStrings )
    {
        foreach ($lineStrings as $lineString) {
            if ( ! $lineString instanceof LineString) {
                throw new \InvalidArgumentException( 'Must be instantiated with an array of lineStrings' );
            }
        }

        $this->lineStrings = $lineStrings;
    }

    /**
     * @return string the Well-Known Text representation of the geometry
     */
    public function toWkt()
    {
        return 'MULTILINESTRING' . (string) $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $return = [ ];
        foreach ($this->lineStrings as $line) {
            $return[] = $line->toArray();
        }

        return $return;
    }

    /**
     * @return Point[] gets all the points in a geometry. Note, order is not necessarily representative.
     */
    public function getPoints()
    {
        $points = [ ];
        foreach ($this->lineStrings as $line) {
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

    public function __toString()
    {
        return '(' . implode( ',', $this->lineStrings ) . ')';
    }


}