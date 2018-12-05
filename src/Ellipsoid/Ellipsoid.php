<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 10:07.
 */

namespace Ricklab\Location\Ellipsoid;

use Ricklab\Location\Location;

abstract class Ellipsoid implements EllipsoidInterface
{

    abstract protected function getRadiusInMetres(): float;

    abstract protected function getMinorSemiAxisInMetres(): float;

    abstract protected function getMajorSemiAxisInMetres(): float;

    /**
     * Returns the average radius of the ellipsoid in specified units.
     *
     * @param string $unit can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     */
    public function radius(string $unit = Location::UNIT_METRES): float
    {
        return $this->unitConversion($this->getRadiusInMetres(), $unit);
    }

    /**
     * @param string $unit The unit you want the multiplier of
     *
     * @return float The multiplier
     */
    public function getMultiplier(string $unit = Location::UNIT_METRES): float
    {
        try {
            return self::MULTIPLIERS[self::KEYS[\mb_strtolower($unit)]];
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Unit '.$unit.' is not a recognised unit.');
        }
    }

    /**
     * @param $distance float The distance in kilometres, can be 'km', 'miles', 'metres', 'feet', 'yards', 'nautical miles'
     * @param $unit string the unit to be converted to
     *
     * @return float the distance in the new unit
     */
    protected function unitConversion(float $distance, string $unit): float
    {
        return $distance * $this->getMultiplier($unit);
    }

    /**
     * @param string $unit unit of measurement
     */
    public function getMajorSemiAxis(string $unit = Location::UNIT_METRES): float
    {
        if ('m' !== $unit) {
            return $this->unitConversion($this->getMajorSemiAxisInMetres() / 1000, $unit);
        }

        return $this->getMajorSemiAxisInMetres();
    }

    /**
     * @param string $unit unit of measurement
     */
    public function getMinorSemiAxis(string $unit = Location::UNIT_METRES): float
    {
        if ('m' !== $unit) {
            return $this->unitConversion($this->getMinorSemiAxisInMetres() / 1000, $unit);
        }

        return $this->getMinorSemiAxisInMetres();
    }

    public function getFlattening(): float
    {
        return ($this->getMajorSemiAxis() - $this->getMinorSemiAxis()) / $this->getMajorSemiAxis();
    }
}
