<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

final class NativeUnitConverter implements UnitConverter
{
    public function convert(float|string $distance, Unit $from, Unit $to): float
    {
        $m = $distance / $from->value;

        return $m * $to->value;
    }

    /**
     * A micro-optimised static method for converting from meters.
     */
    public function convertFromMeters(float|string $distance, Unit $to): float
    {
        return $distance * $to->value;
    }
}
