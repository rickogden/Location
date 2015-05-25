<?php
namespace Ricklab\Location;

class Mars extends Planet
{
    protected static $_radius = array('km' => 3376, 'miles' => 2097);

    public function radius($unit = 'km', $location = null)
    {
        if (isset(self::$_radius[$unit])) {
            return self::$_radius[$unit];
        } else {
            throw new \InvalidArgumentException('Argument is not a valid unit');
        }
    }

}