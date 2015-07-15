<?php
/**
 * Author: rick
 * Date: 14/07/15
 * Time: 13:39
 */

namespace Ricklab\Location;


class LineString implements Geometry, \SeekableIterator, \ArrayAccess
{

    /**
     * @var Point[]
     */
    protected $points = [ ];

    protected $position = 0;

    /**
     * @param Point|array $points the points, or the starting point
     * @param Point|null $end the end point, only used if $points is not an array
     */
    public function __construct( $points, Point $end = null )
    {

        if ($points instanceof Point && $end !== null) {
            $this->points = [ $points, $end ];
        } elseif (is_array( $points ) && count( $points ) > 1) {
            foreach ($points as $point) {
                if ($point instanceof Point) {
                    $this->points[] = $point;

                } else {
                    throw new \InvalidArgumentException( 'Contents of array must consist only of Point objects.' );
                }
            }
        } else {
            throw new \InvalidArgumentException( 'Parameters must be 2 points or an array of points.' );
        }

    }

    /**
     * @return float The initial bearing from the first to second point
     */
    public function getInitialBearing()
    {
        return $this->points[0]->initialBearingTo( $this->points[1] );
    }


    public function getLength( $unit = 'km', $formula = null )
    {
        $distance = 0;
        for ($i = 1; $i < count( $this->points ); $i ++) {
            $distance += $this->points[$i - 1]->distanceTo( $this->points[$i], $unit, $formula );
        }

        return $distance;
    }


    /**
     * @return Point the first point
     */
    public function getFirst()
    {
        return $this->points[0];
    }

    /**
     * @return Point the last point
     */
    public function getLast()
    {
        return $this->points[count( $this->points ) - 1];
    }


    public function jsonSerialize()
    {
        $coordinates = array();
        /** @var Point $point */
        foreach ($this->points as $point) {
            $coordinates[] = [ $point->getLongitude(), $point->getLatitude() ];
        }

        return [ 'type' => 'LineString', 'coordinates' => $coordinates ];
    }

    public function toSql()
    {
        $retVal     = 'LineString(';
        $pointArray = array();
        /** @var Point $point */
        foreach ($this->points as $point) {
            $pointArray[] = (string) $point;
        }

        $retVal .= implode( ', ', $pointArray );
        $retVal .= ')';

        return $retVal;
    }

    public function toArray()
    {
        return $this->points;
    }

    public function seek( $position )
    {
        $this->position = $position;

        if ( ! $this->valid()) {
            throw new \OutOfBoundsException( 'Item does not exist' );
        }
    }

    public function current()
    {
        return $this->points[$this->position];
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

    public function valid()
    {
        return isset( $this->points[$this->position] );
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
        return isset( $this->points[$offset] );
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
        return $this->points[$offset];
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
        if ($value instanceof Point) {
            if (is_integer( $offset ) || $offset === null) {
                $this->points[$offset] = $value;
            }
        }
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
        unset( $this->points[$offset] );
    }


}