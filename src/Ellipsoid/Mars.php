<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

final class Mars extends Ellipsoid
{
    protected const RADIUS = 3376000;

    protected const MAJOR_SEMI_AXIS = 3396200;

    protected const MINOR_SEMI_AXIS = 3376200;

    public function __construct()
    {
        parent::__construct(self::RADIUS, self::MAJOR_SEMI_AXIS, self::MINOR_SEMI_AXIS);
    }
}
