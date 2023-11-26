<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

final class Moon extends Ellipsoid
{
    protected const RADIUS = 1737400;

    protected const MAJOR_SEMI_AXIS = 1738100;

    protected const MINOR_SEMI_AXIS = 1736000;

    public function __construct()
    {
        parent::__construct(self::RADIUS, self::MAJOR_SEMI_AXIS, self::MINOR_SEMI_AXIS);
    }
}
