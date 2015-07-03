<?php

/**
 * The radius of the Earth
 *
 * @author rick
 */

namespace Ricklab\Location;

class Earth extends Planet
{

    protected $radius = 6371.009;

    protected $semiMajorAxis = 6378137;

    /**
     * @var float The radius at the poles in metres (for use in vincenty)
     */
    protected $semiMinorAxis = 6356752.314245;

    /**
     * @var float The flattening of the planet (for use in vincenty)
     */
    protected $flattening = 1 / 298.257223563;
}