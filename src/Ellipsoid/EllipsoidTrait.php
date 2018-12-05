<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

/**
 * @author Rick Ogden <rick@rickogden.com>
 */
trait EllipsoidTrait
{
    protected function getRadiusInMetres(): float
    {
        return self::RADIUS;
    }

    protected function getMinorSemiAxisInMetres(): float
    {
        return self::MINOR_SEMI_AXIS;
    }

    protected function getMajorSemiAxisInMetres(): float
    {
        return self::MAJOR_SEMI_AXIS;
    }
}
