<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Location;

class Polygon implements GeometryInterface, \ArrayAccess, \SeekableIterator
{
    /**
     * @var LineString[]
     */
    protected $lineStrings;

    protected $position = 0;

    /**
     * Pass in an array of Points to create a Polygon or multiple arrays of points for a Polygon with holes in.
     *
     * @param LineString[]|Point[]|array $lines
     */
    public function __construct(array $lines)
    {
        foreach ($lines as $line) {
            if ($line instanceof LineString) {
                $this->lineStrings[] = $line;
            } elseif (\is_array($line)) {
                $this->lineStrings[] = new LineString($line);
            } elseif ($line instanceof Point) {
                $this->lineStrings[] = new LineString($lines);
                break;
            }
        }

        foreach ($this->lineStrings as $line) {
            if ($line->getLast() != $line->getFirst()) {
                $line[] = $line->getFirst();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '('.\implode(',', $this->lineStrings).')';
    }

    /**
     * {@inheritdoc}
     */
    public function toWkt(): string
    {
        return 'POLYGON'.$this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'Polygon',
            'coordinates' => $this->toArray(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $return = [];
        foreach ($this->lineStrings as $line) {
            $return[] = $line->toArray();
        }

        return $return;
    }

    /**
     * The length of the perimeter of the outer-most polygon in unit specified.
     *
     * @param string   $unit
     * @param null|int $formula defaults to Location::$defaultFormula
     *
     * @return float
     */
    public function getPerimeter($unit = 'km', $formula = null)
    {
        return $this->lineStrings[0]->getLength($unit, $formula);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element.
     *
     * @see http://php.net/manual/en/iterator.current.php
     *
     * @return LineString
     */
    public function current()
    {
        return $this->lineStrings[$this->position];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element.
     *
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        ++$this->position ;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element.
     *
     * @see http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element.
     *
     * @see http://php.net/manual/en/iterator.rewind.php
     */
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
        return isset($this->lineStrings[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param number $offset <p>
     *                       The offset to retrieve.
     *                       </p>
     *
     * @return LineString
     */
    public function offsetGet($offset)
    {
        return $this->lineStrings[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param number     $offset <p>
     *                           The offset to assign the value to.
     *                           </p>
     * @param lineString $value  <p>
     *                           The value to set.
     *                           </p>
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof LineString) {
            if (null === $offset) {
                $this->lineStrings[] = $value;
            } elseif (\is_integer($offset)) {
                $this->lineStrings[$offset] = $value;
            } else {
                throw new \OutOfBoundsException('Key must be numeric.');
            }
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     */
    public function offsetUnset($offset)
    {
        unset($this->lineStrings[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Seeks to a position.
     *
     * @see http://php.net/manual/en/seekableiterator.seek.php
     *
     * @param int $position <p>
     *                      The position to seek to.
     *                      </p>
     */
    public function seek($position)
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException('Item does not exist');
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid.
     *
     * @see http://php.net/manual/en/iterator.valid.php
     *
     * @return bool the return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure
     */
    public function valid()
    {
        return isset($this->lineStrings[$this->position]);
    }

    public function getPoints(): array
    {
        $points = [];
        foreach ($this->lineStrings as $line) {
            $linePoints = $line->getPoints();
            $points += $linePoints;
        }

        return $points;
    }

    public function getBBox()
    {
        return Location::getBBox($this);
    }
}
