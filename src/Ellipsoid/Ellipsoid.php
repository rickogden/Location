<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Converter\UnitConverter;

class Ellipsoid implements EllipsoidInterface
{
    private float|int $radius;
    private float|int $majorSemiAxis;
    private float|int $minorSemiAxis;
    private float|int|null $flattening = null;

    public static function fromRadius(float|int $radius): self
    {
        return new self($radius, $radius, $radius);
    }

    public static function fromSemiAxes(float|int $majorSemiAxis, float|int $minorSemiAxis): self
    {
        return new self(($majorSemiAxis + $minorSemiAxis) / 2, $majorSemiAxis, $minorSemiAxis);
    }

    /**
     * @param float|int $radius        in meters
     * @param float|int $majorSemiAxis in meters
     * @param float|int $minorSemiAxis in meters
     */
    public function __construct(float|int $radius, float|int $majorSemiAxis, float|int $minorSemiAxis)
    {
        $this->radius = $radius;
        $this->majorSemiAxis = $majorSemiAxis;
        $this->minorSemiAxis = $minorSemiAxis;
    }

    public function radius(string $unit = UnitConverter::UNIT_METERS): float|int
    {
        return UnitConverter::convertFromMeters($this->radius, $unit);
    }

    public function majorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float|int
    {
        return UnitConverter::convertFromMeters($this->majorSemiAxis, $unit);
    }

    public function minorSemiAxis(string $unit = UnitConverter::UNIT_METERS): float|int
    {
        return UnitConverter::convertFromMeters($this->minorSemiAxis, $unit);
    }

    public function flattening(): float|int
    {
        if (null === $this->flattening) {
            $this->flattening = ($this->majorSemiAxis - $this->minorSemiAxis) / $this->majorSemiAxis;
        }

        return $this->flattening;
    }

    public function equals(Ellipsoid $ellipsoid): bool
    {
        return $ellipsoid === $this || ((float) $this->radius === (float) $ellipsoid->radius
            && (float) $this->majorSemiAxis === (float) $ellipsoid->majorSemiAxis
            && (float) $this->minorSemiAxis === (float) $ellipsoid->minorSemiAxis);
    }
}
