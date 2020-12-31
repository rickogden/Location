<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator\Traits;

trait GeoSpatialExtensionTrait
{
    private static bool $useSpatialExtension = true;

    public static function enableGeoSpatialExtension(): void
    {
        self::$useSpatialExtension = true;
    }

    public static function disableGeoSpatialExtension(): void
    {
        self::$useSpatialExtension = false;
    }
}
