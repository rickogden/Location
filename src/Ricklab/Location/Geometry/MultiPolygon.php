<?php
/**
 * Author: rick
 * Date: 04/10/15
 * Time: 11:22
 */

namespace Ricklab\Location\Geometry;


class MultiPolygon implements GeometryInterface
{
    /**
     * @var Polygon[]
     */
    protected $geometries = [];

    /**
     * @param Polygon[] $polygons
     */
    public function __construct(array $polygons)
    {

        foreach ($polygons as $polygon) {
            if (is_array($polygon)) {
                $polygon = new Polygon($polygon);
            }
            if ( ! $polygon instanceof Polygon) {
                throw new \InvalidArgumentException('$polygons must be an array of Polygon objects');
            } else {
                $this->geometries[] = $polygon;
            }
        }

    }

    /**
     * @return string the Well-Known Text representation of the geometry
     */
    public function toWkt()
    {
        return 'MULTIPOLYGON' . (string) $this;
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
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        $json = [
            'type'        => 'MultiPolygon',
            'coordinates' => $this->toArray()
        ];

        return $json;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $ar = [];

        foreach ($this->geometries as $polygon) {
            $ar[] = $polygon->toArray();
        }

        return $ar;
    }

    public function __toString()
    {

        return '(' . implode(',', $this->geometries) . ')';
    }


}