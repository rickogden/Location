<?php
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 16:47
 */

namespace Ricklab\Location\Feature;


use Ricklab\Location\Location;

class FeatureCollection extends FeatureAbstract implements \SeekableIterator
{
    /**
     * @var Feature[]
     */
    protected $features = [ ];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * FeatureCollection constructor.
     *
     * @param Feature[] $features
     * @param bool $bbox
     */
    public function __construct( array $features, $bbox = false )
    {
        $this->setFeatures( $features );
        $this->bbox = (bool) $bbox;
    }

    /**
     * @param Feature[] $features
     */
    public function setFeatures( array $features )
    {
        foreach ($features as $feature) {
            if ( ! $feature instanceof Feature) {
                throw new \InvalidArgumentException( 'Only instances of Feature can be passed in the array.' );
            }
        }
        $this->features = $features;
    }

    public function enableBBox()
    {
        $this->bbox = true;
    }

    public function disableBBox()
    {
        $this->bbox = false;
    }

    public function addFeature( Feature $feature )
    {
        $this->features[] = $feature;
    }

    public function removeFeature( Feature $feature )
    {
        foreach ($this->features as $i => $f) {
            if ($f === $feature) {
                unset( $this->features[$i] );
            }
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->features[$this->position];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->position ++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset( $this->features[$this->position] );
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Seeks to a position
     * @link http://php.net/manual/en/seekableiterator.seek.php
     *
     * @param int $position <p>
     * The position to seek to.
     * </p>
     *
     * @return void
     */
    public function seek( $position )
    {
        $this->position = $position;
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
        $features = [ ];
        $points   = [ ];
        foreach ($this->features as $feature) {
            $features[] = $feature->jsonSerialize();
            if ($this->bbox) {
                $points += $feature->getGeometry()->getPoints();
            }
        }


        $return         = [];
        $return['type'] = 'FeatureCollection';

        if ($this->bbox) {
            $return['bbox'] = Location::getBBoxArray( $points );
        }

        $return['features'] = $features;


        return $return;
    }


}