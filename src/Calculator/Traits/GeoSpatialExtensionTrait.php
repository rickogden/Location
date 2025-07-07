<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator\Traits;

trait GeoSpatialExtensionTrait
{
    public function __construct(private bool $useSpatialExtension = true)
    {
    }

    public function enableGeoSpatialExtension(): void
    {
        $this->useSpatialExtension = true;
    }

    public function disableGeoSpatialExtension(): void
    {
        $this->useSpatialExtension = false;
    }
}
