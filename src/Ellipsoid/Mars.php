<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

/**
 * @psalm-immutable
 */
class Mars extends Ellipsoid
{
    use EllipsoidTrait;
    protected const RADIUS = 3376000;

    protected const MAJOR_SEMI_AXIS = 3396200;

    protected const MINOR_SEMI_AXIS = 3376200;
}
