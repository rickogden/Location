<?php
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 10:07
 */

namespace Ricklab\Location;


abstract class Planet
{

    /**
     * @var array Unit multipliers relative to km
     */
    protected $multipliers = [
        'km'             => 1,
        'miles'          => 0.62137119,
        'metres'         => 1000,
        'feet'           => 3280.8399,
        'yards'          => 1093.6133,
        'nautical miles' => 0.5399568
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
     * @var float radius in kilometres
     */
    protected $radius;

    /**
     * @param string $unit can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     * @param mixed $location can either be a latitude (float) or a Point object. Not used currently.
     * @return mixed
     */
    public function radius($unit = 'km', $location = null) {
        return $this->unitConversion($this->radius, $unit);
    }

    /**
     * @param string $unit The unit you want the multiplier of
     *
     * @return float The multiplier
     */
    public function getMultiplier( $unit )
    {
        try {
            return $this->multipliers[$this->keys[$unit]];
        } catch ( \Exception $e ) {
            throw new \InvalidArgumentException( 'Unit ' . $unit . ' is not a recognised unit.' );
        }
    }

    /**
     * @param $distance float The distance in kilometres, can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     * @param $unit string the unit to be converted to
     * @return float the distance in the new unit
     */
    protected function unitConversion($distance, $unit) {

        return $distance * $this->getMultiplier( $unit );
    }

} 