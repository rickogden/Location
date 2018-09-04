<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 14/07/15
 * Time: 13:39.
 */

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Location;

/**
 * Class LineString.
 */
class LineString implements GeometryInterface, \SeekableIterator, \ArrayAccess, \Countable
{
    /**
     * @var Point[]
     */
    protected $points = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @param Point|Point[] $points the points, or the starting point
     * @param Point|null    $end    the end point, only used if $points is not an array
     */
    public function __construct($points, Point $end = null)
    {
        if ($points instanceof Point && null !== $end) {
            $this->points = [$points, $end];
        } elseif (\is_array($points) && \count($points) > 1) {
            foreach ($points as $point) {
                $this[] = $point;
            }
        } else {
            throw new \InvalidArgumentException('Parameters must be 2 points or an array of points.');
        }
    }

    /**
     * @return float The initial bearing from the first to second point
     */
    public function getInitialBearing()
    {
        return $this->points[0]->initialBearingTo($this->points[1]);
    }

    public function getLength($unit = 'km', $formula = null)
    {
        $distance = 0;

        for ($i = 1; $i < \count($this->points); ++$i ) {
            $distance += $this->points[$i - 1]->distanceTo($this->points[$i], $unit, $formula);
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
        return $this->points[\count($this->points) - 1];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return '('.\implode(', ', $this->points).')';
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $coordinates = $this->toArray();

        return ['type' => 'LineString', 'coordinates' => $coordinates];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $return = [];
        foreach ($this->points as $point) {
            $return[] = $point->toArray();
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function toWkt()
    {
        return 'LINESTRING'.$this;
    }

    public function seek($position)
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException('Item does not exist');
        }
    }

    public function valid()
    {
        return isset($this->points[$this->position]);
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
        ++$this->position ;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned
     */
    public function offsetExists($offset)
    {
        return isset($this->points[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return Point
     */
    public function offsetGet($offset)
    {
        return $this->points[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param int   $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param point $value  <p>
     *                      The value to set.
     *                      </p>
     */
    public function offsetSet($offset, $value)
    {
        if (\is_array($value)) {
            $value = new Point($value);
        }

        if ($value instanceof Point) {
            if (\is_integer($offset)) {
                $this->points[$offset] = $value;
            } elseif (null === $offset) {
                $this->points[] = $value;
            } else {
                throw new \OutOfBoundsException('Key must be numeric.');
            }
        } else {
            throw new \InvalidArgumentException('Value must be a point or an array');
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param int $offset <p>
     *                    The offset to unset.
     *                    </p>
     */
    public function offsetUnset($offset)
    {
        unset($this->points[$offset]);
    }

    /**
     * Gets the bounding box which will contain the entire geometry.
     *
     * @return Polygon
     */
    public function getBBox()
    {
        return Location::getBBox($this);
    }

    /**
     * Converts LineString into a Polygon.
     *
     * @return Polygon
     */
    public function toPolygon()
    {
        return new Polygon($this->getPoints());
    }

    /**
     * {@inheritdoc}
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Reverses the direction of the line.
     *
     * @return $this
     */
    public function reverse()
    {
        $this->points = \array_reverse($this->points);

        return $this;
    }

    /**
     * Count elements of an object.
     *
     * @see https://php.net/manual/en/countable.count.php
     *
     * @return int the custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer
     *
     * @since 5.1.0
     */
    public function count()
    {
        return \count($this->points);
    }
}
