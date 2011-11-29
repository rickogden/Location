<?php

/**
 * Description of EarthRadius
 *
 * @author rick
 */
class Location_Earth {

    protected static $_radius = array('km' => 6371, 'miles' => 3959);

    public static function radius($unit = 'km') {
        if (isset(self::$_radius[$unit])) {
            return self::$_radius[$unit];
        } else {
            throw new InvalidArgumentException('Argument is not a valid unit');
        }
    }

}