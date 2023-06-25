<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

/**
 * @psalm-immutable
 */
class Moon extends Ellipsoid
{
    use EllipsoidTrait;
    protected const RADIUS = 1737400;

    protected const MAJOR_SEMI_AXIS = 1738100;

    protected const MINOR_SEMI_AXIS = 1736000;
}
