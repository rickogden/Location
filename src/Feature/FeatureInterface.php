<?php

declare(strict_types=1);

namespace Ricklab\Location\Feature;

interface FeatureInterface extends \JsonSerializable
{
    /**
     * @param bool|true $bbox
     */
    public function withBbox(bool $bbox = true): void;
}
