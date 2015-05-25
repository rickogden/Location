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
     * @param $distance float The distance in kilometres, can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     * @param $unit string the unit to be converted to
     * @return float the distance in the new unit
     */
    protected function unitConversion($distance, $unit) {
        switch($unit) {
            case 'km':
                return $distance;
            break;
            case 'miles':
                return $distance * 0.621;
            break;
            case 'm':
            case 'metres':
            case 'meters':
                return $distance * 1000;
            break;
            case 'ft':
            case 'feet':
            case 'foot':
                return $distance * 3280.840;
            break;
            case 'yards':
            case 'yds':
                return $distance * 1093.610;
            break;
            case 'nm':
            case 'nautical miles':
                return $distance * 0.540;
            default:
                throw new \InvalidArgumentException('Unit '.$unit.' is not a recognised unit.');

        }
    }

} 