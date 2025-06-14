<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

class Ellipsoid implements EllipsoidInterface
{
    /** @var float|numeric-string|null */
    private float|string|null $flattening = null;

    public static function fromRadius(float|int $radius): self
    {
        return new self($radius, $radius, $radius);
    }

    public static function fromSemiAxes(float|int $majorSemiAxis, float|int $minorSemiAxis): self
    {
        return new self(($majorSemiAxis + $minorSemiAxis) / 2, $majorSemiAxis, $minorSemiAxis);
    }

    /**
     * @param float|numeric-string $radius        in meters
     * @param float|numeric-string $majorSemiAxis in meters
     * @param float|numeric-string $minorSemiAxis in meters
     */
    public function __construct(
        private readonly float|string $radius,
        private readonly float|string $majorSemiAxis,
        private readonly float|string $minorSemiAxis,
    ) {
    }

    public function radius(): float|string
    {
        return $this->radius;
    }

    public function majorSemiAxis(): float|string
    {
        return $this->majorSemiAxis;
    }

    public function minorSemiAxis(): float|string
    {
        return $this->minorSemiAxis;
    }

    public function flattening(): float|string
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
