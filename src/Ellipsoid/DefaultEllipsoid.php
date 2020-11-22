<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

final class DefaultEllipsoid
{
    private static ?EllipsoidInterface $ellipsoid = null;

    public static function set(EllipsoidInterface $ellipsoid): void
    {
        self::$ellipsoid = $ellipsoid;
    }

    public static function get(): EllipsoidInterface
    {
        if (null === self::$ellipsoid) {
            // Default to Earth
            self::$ellipsoid = new Earth();
        }

        return self::$ellipsoid;
    }
}
