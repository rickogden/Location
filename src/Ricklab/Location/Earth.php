<?php

/**
 * The radius of the Earth
 *
 * @author rick
 */

namespace Ricklab\Location;

class Earth extends Planet
{

    protected static $radius = array(
        'km' => 6371.009,
        'miles' => 3958.761,
        'm' => 6371009,
        'nmi' => 3440.069
    );

    public function radius($unit = 'km', $location = null)
    {
        if (isset(self::$radius[$unit])) {
            return self::$radius[$unit];
        } else {
            throw new \InvalidArgumentException('Argument is not a valid unit');
        }
    }

}