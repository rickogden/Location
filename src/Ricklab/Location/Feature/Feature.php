<?php
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 11:14
 */

namespace Ricklab\Location\Feature;


use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Location;

class Feature implements \jsonSerializable, \ArrayAccess
{

    protected $id = null;

    /**
     * @var GeometryInterface
     */
    protected $geometry = null;

    /**
     * @var array
     */
    protected $properties = [ ];

    /**
     * @var bool
     */
    protected $bbox = null;


    public function __construct( array $properties = [ ], GeometryInterface $geometry = null, $bbox = false )
    {
        $this->properties = $properties;
        $this->geometry   = $geometry;
        $this->bbox       = (bool) $bbox;
    }

    public function enableBBox()
    {
        $this->bbox = true;
    }

    public function disableBBox()
    {

    }

    /**
     * @return GeometryInterface
     */
    public function getGeometry()
    {
        return $this->geometry;
    }

    /**
     * @param GeometryInterface $geometry
     *
     * @return $this
     */
    public function setGeometry( GeometryInterface $geometry )
    {
        $this->geometry = $geometry;

        return $this;
    }

    /**
     * @return array all the properties
     */
    public function getProperties()
    {
        return $this->properties;
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


        $array = [
            'type'       => 'Feature',
            'geometry'   => $this->geometry->jsonSerialize(),
            'properties' => $this->properties
        ];

        if ($this->bbox) {
            $array['bbox'] = Location::getBBox( $this->geometry );
        }

        if ($this->id !== null) {
            $array['id'] = $this->id;
        }

        return $array;

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists( $offset )
    {
        return isset( $this->properties[$offset] );
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet( $offset )
    {
        return $this->getProperty( $offset );
    }

    /**
     * @param $key string the key of the property
     *
     * @return mixed the value of the property
     */
    public function getProperty( $key )
    {
        return $this->properties[$key];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @return void
     */
    public function offsetSet( $offset, $value )
    {
        $this->setProperty( $offset, $value );
    }

    /**
     * @param $key string Property key
     * @param $value string Property value
     *
     * @return $this
     */
    public function setProperty( $key, $value )
    {
        $this->properties[$key] = $value;

        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     */
    public function offsetUnset( $offset )
    {
        $this->removeProperty( $offset );
    }

    public function removeProperty( $key )
    {
        unset( $this->properties[$key] );

        return $this;
    }


}