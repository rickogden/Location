<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry\Traits;

use Ricklab\Location\Transformer\GeoJsonTransformer;

trait TransformationTrait
{
    abstract public function __toString(): string;

    public function jsonSerialize(): array
    {
        return GeoJsonTransformer::jsonArray($this);
    }
}
