<?php

declare(strict_types=1);

/**
 * The radius of the Earth.
 *
 * @author rick
 */

namespace Ricklab\Location\Ellipsoid;

class Earth extends Ellipsoid
{
    use EllipsoidTrait;
    protected const RADIUS = 6371009;

    protected const MAJOR_SEMI_AXIS = 6378137;

    protected const MINOR_SEMI_AXIS = 6356752.314245;
}
