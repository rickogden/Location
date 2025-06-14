<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Override;

class Ellipsoid implements EllipsoidInterface
{
    /** @var float|numeric-string|null */
    private float|string|null $flattening = null;

    /**
     * @param float|numeric-string $radius
     */
    public static function fromRadius(float|string $radius): self
    {
        return new self($radius, $radius, $radius);
    }

    /**
     * @param float|numeric-string $majorSemiAxis
     * @param float|numeric-string $minorSemiAxis
     */
    public static function fromSemiAxes(float|string $majorSemiAxis, float|string $minorSemiAxis): self
    {
        return new self(((float) $majorSemiAxis + (float) $minorSemiAxis) / 2.0, $majorSemiAxis, $minorSemiAxis);
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

    #[Override]
    public function radius(): float|string
    {
        return $this->radius;
    }

    #[Override]
    public function majorSemiAxis(): float|string
    {
        return $this->majorSemiAxis;
    }

    #[Override]
    public function minorSemiAxis(): float|string
    {
        return $this->minorSemiAxis;
    }

    #[Override]
    public function flattening(): float|string
    {
        if (null === $this->flattening) {
            $this->flattening = ((float) $this->majorSemiAxis - (float) $this->minorSemiAxis) / (float) $this->majorSemiAxis;
        }

        return $this->flattening;
    }

    #[Override]
    public function equals(Ellipsoid $ellipsoid): bool
    {
        return $ellipsoid === $this || ((float) $this->radius === (float) $ellipsoid->radius
                && (float) $this->majorSemiAxis === (float) $ellipsoid->majorSemiAxis
                && (float) $this->minorSemiAxis === (float) $ellipsoid->minorSemiAxis);
    }
}
