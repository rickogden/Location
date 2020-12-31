<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

/**
 * @author Rick Ogden <rick@rickogden.com>
 */
trait EllipsoidTrait
{
    protected static function getRadiusInMeters(): float
    {
        return self::RADIUS;
    }

    protected static function getMinorSemiAxisInMeters(): float
    {
        return self::MINOR_SEMI_AXIS;
    }

    protected static function getMajorSemiAxisInMeters(): float
    {
        return self::MAJOR_SEMI_AXIS;
    }
}
