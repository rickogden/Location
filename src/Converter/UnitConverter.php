<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

interface UnitConverter
{
    /**
     * @param float|numeric-string $distance
     *
     * @return float|numeric-string
     */
    public function convert(float|string $distance, Unit $from, Unit $to): float|string;

    /**
     * A micro-optimised static method for converting from meters.
     *
     * @param float|numeric-string $distance
     *
     * @return float|numeric-string
     */
    public function convertFromMeters(float|string $distance, Unit $to): float|string;
}
