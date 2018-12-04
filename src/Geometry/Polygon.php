<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Ricklab\Location\Location;

class Polygon implements GeometryInterface, \SeekableIterator
{
    /**
     * @var LineString[]
     */
    protected $lineStrings;

    protected $position = 0;

    public static function fromArray(array $geometries): self
    {
        $result = [];
        foreach ($geometries as $lineString) {
            if ($lineString instanceof LineString) {
                $result[] = $lineString;
            } else {
                $result[] = LineString::fromArray($lineString);
            }
        }

        return new self($result);
    }

    public static function fromLineString(LineString $lineString): self
    {
        return new self([$lineString]);
    }

    /**
     * Pass in an array of Points to create a Polygon or multiple arrays of points for a Polygon with holes in.
     *
     * @param LineString[]
     */
    public function __construct(array $lines)
    {
        $this->lineStrings = (function (LineString ...$lineStrings) {
            return $lineStrings;
        })(...$lines);

        foreach ($this->lineStrings as $line) {
            if ((string) $line->getLast() !== (string) $line->getFirst()) {
                $line->add($line->getFirst());
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

    /**
     * @return LineString[]
     */
    public function getLineStrings(): array
    {
        return $this->lineStrings;
    }
}
