<?php

class Location_Mars {
    protected static $_radius = array('km' => 3376, 'miles' => 2097);

    public static function radius($unit = 'km') {
        if (isset(self::$_radius[$unit])) {
            return self::$_radius[$unit];
        } else {
            throw new InvalidArgumentException('Argument is not a valid unit');
        }
    }

}