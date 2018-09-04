<?php
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 10:07
 */

namespace Ricklab\Location\Ellipsoid;

abstract class Ellipsoid
{

    /**
     * @var array Unit multipliers relative to km
     */
    protected $multipliers = [
        'km'             => 0.001,
        'miles'          => 0.00062137119,
        'metres'         => 1,
        'feet'           => 3.2808399,
        'yards'          => 1.0936133,
        'nautical miles' => 0.0005399568
    ];

    /**
     * @var array Key translations for multipliers
     */
    protected $keys = [
        'km'             => 'km',
        'kilometres'     => 'km',
        'kilometers'     => 'km',
        'miles'          => 'miles',
        'metres'         => 'metres',
        'meters'         => 'metres',
        'm'              => 'metres',
        'feet'           => 'feet',
        'ft'             => 'feet',
        'foot'           => 'feet',
        'yards'          => 'yards',
        'yds'            => 'yards',
        'nautical miles' => 'nautical miles',
        'nm'             => 'nautical miles'
    ];


    /**
     * @var float radius in metres (for use in Haversine)
     */
    protected $radius;

    /**
     * @var float The radius at the equator in metres (for use in Vincenty)
     */
    protected $majorSemiAxis;

    /**
     * @var float The radius at the poles in metres (for use in Vincenty)
     */
    protected $minorSemiAxis;

    /**
     * Returns the average radius of the ellipsoid in specified units.
     *
     * @param string $unit can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     *
     * @return mixed
     */
    public function radius($unit = 'km')
    {
        return $this->unitConversion($this->radius, $unit);
    }

    /**
     * @param string $unit The unit you want the multiplier of
     *
     * @return float The multiplier
     */
    public function getMultiplier($unit)
    {
        try {
            return $this->multipliers[$this->keys[strtolower($unit)]];
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Unit ' . $unit . ' is not a recognised unit.');
        }
    }

    /**
     * @param $distance float The distance in kilometres, can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     * @param $unit string the unit to be converted to
     *
     * @return float the distance in the new unit
     */
    protected function unitConversion($distance, $unit)
    {
        return $distance * $this->getMultiplier($unit);
    }

    /**
     * @param string $unit unit of measurement
     *
     * @return float
     */
    public function getMajorSemiAxis($unit = 'm')
    {
        if ($unit !== 'm') {
            return $this->unitConversion($this->majorSemiAxis / 1000, $unit);
        }

        return $this->majorSemiAxis;
    }

    /**
     * @param string $unit unit of measurement
     *
     * @return float
     */
    public function getMinorSemiAxis($unit = 'm')
    {
        if ($unit !== 'm') {
            return $this->unitConversion($this->minorSemiAxis / 1000, $unit);
        }

        return $this->minorSemiAxis;
    }

    /**
     * @return float
     */
    public function getFlattening()
    {
        return ($this->getMajorSemiAxis() - $this->getMinorSemiAxis()) / $this->getMajorSemiAxis();
    }
}
