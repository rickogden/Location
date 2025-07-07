<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

use Override;

final class NativeUnitConverter implements UnitConverter
{
    #[Override]
    public function convert(float|string $distance, Unit $from, Unit $to): float
    {
        $m = (float) $distance / (float) $from->value;

        return $m * (float) $to->value;
    }

    /**
     * A micro-optimised static method for converting from meters.
     */
    #[Override]
    public function convertFromMeters(float|string $distance, Unit $to): float
    {
        return (float) $distance * (float) $to->value;
    }
}
