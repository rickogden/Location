<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 10:07.
 */

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Location;

abstract class Ellipsoid
{
    /**
     * @var array Unit multipliers relative to km
     */
    protected const MULTIPLIERS = [
        Location::UNIT_KM => 0.001,
        Location::UNIT_MILES => 0.00062137119,
        Location::UNIT_METRES => 1,
        Location::UNIT_FEET => 3.2808399,
        Location::UNIT_YARDS => 1.0936133,
        Location::UNIT_NAUTICAL_MILES => 0.0005399568,
    ];

    /**
     * @var array Key translations for multipliers
     */
    protected const KEYS = [
        'km' => Location::UNIT_KM,
        'kilometres' => Location::UNIT_KM,
        'kilometers' => Location::UNIT_KM,
        'miles' => Location::UNIT_MILES,
        'metres' => Location::UNIT_METRES,
        'meters' => Location::UNIT_METRES,
        'm' => Location::UNIT_METRES,
        'feet' => Location::UNIT_FEET,
        'ft' => Location::UNIT_FEET,
        'foot' => Location::UNIT_FEET,
        'yards' => Location::UNIT_YARDS,
        'yds' => Location::UNIT_YARDS,
        'nautical miles' => Location::UNIT_NAUTICAL_MILES,
        'nm' => Location::UNIT_NAUTICAL_MILES,
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
     */
    public function radius($unit = 'km'): float
    {
        return $this->unitConversion($this->radius, $unit);
    }

    /**
     * @param string $unit The unit you want the multiplier of
     *
     * @return float The multiplier
     */
    public function getMultiplier($unit): float
    {
        try {
            return self::MULTIPLIERS[self::KEYS[\mb_strtolower($unit)]];
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Unit '.$unit.' is not a recognised unit.');
        }
    }

    /**
     * @param $distance float The distance in kilometres, can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     * @param $unit string the unit to be converted to
     *
     * @return float the distance in the new unit
     */
    protected function unitConversion($distance, $unit): float
    {
        return $distance * $this->getMultiplier($unit);
    }

    /**
     * @param string $unit unit of measurement
     */
    public function getMajorSemiAxis($unit = 'm'): float
    {
        if ('m' !== $unit) {
            return $this->unitConversion($this->majorSemiAxis / 1000, $unit);
        }

        return $this->majorSemiAxis;
    }

    /**
     * @param string $unit unit of measurement
     */
    public function getMinorSemiAxis($unit = 'm'): float
    {
        if ('m' !== $unit) {
            return $this->unitConversion($this->minorSemiAxis / 1000, $unit);
        }

        return $this->minorSemiAxis;
    }

    public function getFlattening(): float
    {
        return ($this->getMajorSemiAxis() - $this->getMinorSemiAxis()) / $this->getMajorSemiAxis();
    }
}
