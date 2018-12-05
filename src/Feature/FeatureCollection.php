<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 16:47.
 */

namespace Ricklab\Location\Feature;

use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Location;

class FeatureCollection extends FeatureAbstract implements \IteratorAggregate
{
    /**
     * @var Feature[]
     */
    protected $features = [];

    /**
     * FeatureCollection constructor.
     *
     * @param Feature[] $features
     * @param bool      $bbox
     */
    public function __construct(array $features = [], $bbox = false)
    {
        $this->setFeatures($features);
        $this->bbox = (bool) $bbox;
    }

    /**
     * @param Feature[] $features
     */
    public function setFeatures(array $features): void
    {
        foreach ($features as $feature) {
            if (!$feature instanceof Feature) {
                throw new \InvalidArgumentException('Only instances of Feature can be passed in the array.');
            }
        }
        $this->features = $features;
    }

    public function enableBBox(): void
    {
        $this->bbox = true;
    }

    public function disableBBox(): void
    {
        $this->bbox = false;
    }

    public function addFeature(Feature $feature): void
    {
        $this->features[] = $feature;
    }

    public function removeFeature(Feature $feature): void
    {
        foreach ($this->features as $i => $f) {
            if ($f === $feature) {
                unset($this->features[$i]);
            }
        }
    }

    public function jsonSerialize(): array
    {
        $features = [];
        $points = [];
        foreach ($this->features as $feature) {
            $features[] = $feature->jsonSerialize();

            if ($this->bbox) {
                $points[] = $feature->getGeometry()->getPoints();
            }
        }

        if ($points) {
            $points = \array_merge(...$points);
        }

        $return = [];
        $return['type'] = 'FeatureCollection';

        if ($this->bbox) {
            $return['bbox'] = Location::getBBoxArray(new LineString($points));
        }

        $return['features'] = $features;

        return $return;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->features);
    }
}
