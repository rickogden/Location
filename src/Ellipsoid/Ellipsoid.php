<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Converter\Unit;
use Ricklab\Location\Converter\UnitConverterRegistry;

class Ellipsoid implements EllipsoidInterface
{
    /** @var float|numeric-string */
    private float|string $radius;

    /** @var float|numeric-string */
    private float|string $majorSemiAxis;

    /** @var float|numeric-string */
    private float|string $minorSemiAxis;

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
    public function __construct(float|string $radius, float|string $majorSemiAxis, float|string $minorSemiAxis)
    {
        $this->radius = $radius;
        $this->majorSemiAxis = $majorSemiAxis;
        $this->minorSemiAxis = $minorSemiAxis;
    }

    public function radius(Unit $unit = Unit::METERS): float|string
    {
        return UnitConverterRegistry::getUnitConverter()->convertFromMeters($this->radius, $unit);
    }

    public function majorSemiAxis(Unit $unit = Unit::METERS): float|string
    {
        return UnitConverterRegistry::getUnitConverter()->convertFromMeters($this->majorSemiAxis, $unit);
    }

    public function minorSemiAxis(Unit $unit = Unit::METERS): float|string
    {
        return UnitConverterRegistry::getUnitConverter()->convertFromMeters($this->minorSemiAxis, $unit);
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
