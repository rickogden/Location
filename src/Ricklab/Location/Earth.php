<?php

/**
 * The radius of the Earth
 *
 * @author rick
 */

namespace Ricklab\Location;

class Earth
{

    protected static $_radius = array(
        'km' => 6371.009,
        'miles' => 3958.761,
        'm' => 6371009,
        'nmi' => 3440.069
    );

    public static function radius($unit = 'km')
    {
        if (isset(self::$_radius[$unit])) {
            return self::$_radius[$unit];
        } else {
            throw new \InvalidArgumentException('Argument is not a valid unit');
        }
    }

}