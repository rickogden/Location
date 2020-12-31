<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

interface UsesGeoSpatialExtensionInterface
{
    public static function enableGeoSpatialExtension(): void;

    public static function disableGeoSpatialExtension(): void;
}
