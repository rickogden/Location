<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

interface EllipsoidInterface
{
    /**
     * @return float|numeric-string in meters
     */
    public function radius(): float|string;

    /**     *
     * @return float|numeric-string in meters
     */
    public function majorSemiAxis(): float|string;

    /**
     * @return float|numeric-string in meters
     */
    public function minorSemiAxis(): float|string;

    /**
     * @return float|numeric-string
     */
    public function flattening(): float|string;

    public function equals(Ellipsoid $ellipsoid): bool;
}
