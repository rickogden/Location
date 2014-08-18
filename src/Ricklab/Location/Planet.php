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
     * @param string $unit can be km, miles, nm or m
     * @param mixed $location can either be a latitude (float) or a Point object
     * @return mixed
     */
    abstract public function radius($unit = 'km', $location = null);

} 