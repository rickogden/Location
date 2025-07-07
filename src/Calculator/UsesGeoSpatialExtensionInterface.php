<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

interface UsesGeoSpatialExtensionInterface
{
    public function enableGeoSpatialExtension(): void;

    public function disableGeoSpatialExtension(): void;
}
