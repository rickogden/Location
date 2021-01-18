<?php

declare(strict_types=1);

use Ricklab\Location\Calculator\BearingCalculator;
use Ricklab\Location\Calculator\DistanceCalculator;
use Ricklab\Location\Calculator\EquirectangleCalculator;
use Ricklab\Location\Calculator\HaversineCalculator;
use Ricklab\Location\Calculator\VincentyCalculator;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Geometry\Point;

/**
 * @BeforeMethods({"initPoints"})
 * @Warmup(2)
 * @Revs(100)
 * @Iterations(10)
 */
class PointBench
{
    /**
     * @var Point[]
     */
    private array $points;

    public function enableGeospatialExtension(): void
    {
        HaversineCalculator::enableGeoSpatialExtension();
        VincentyCalculator::enableGeoSpatialExtension();
        BearingCalculator::enableGeoSpatialExtension();
    }

    /**
     * @BeforeMethods({"disableGeospatialExtension", "initPoints"})
     */
    public function disableGeospatialExtension(): void
    {
        HaversineCalculator::disableGeoSpatialExtension();
        VincentyCalculator::disableGeoSpatialExtension();
        BearingCalculator::disableGeoSpatialExtension();
    }

    public function initPoints(): void
    {
        $this->points = [
            new Point(10.40744, 57.64911),
            new Point(-4.333913, 48.666751),
            new Point(-4.33387, 48.666751),
            new Point(-4.39, 48.6),
        ];
    }

    public function runDistanceTo(DistanceCalculator $formula): void
    {
        $this->points[0]->distanceTo($this->points[1], UnitConverter::UNIT_METERS, $formula);
        $this->points[0]->distanceTo($this->points[2], UnitConverter::UNIT_METERS, $formula);
        $this->points[0]->distanceTo($this->points[3], UnitConverter::UNIT_METERS, $formula);
        $this->points[1]->distanceTo($this->points[0], UnitConverter::UNIT_METERS, $formula);
        $this->points[1]->distanceTo($this->points[2], UnitConverter::UNIT_METERS, $formula);
        $this->points[1]->distanceTo($this->points[3], UnitConverter::UNIT_METERS, $formula);
        $this->points[2]->distanceTo($this->points[0], UnitConverter::UNIT_METERS, $formula);
        $this->points[2]->distanceTo($this->points[1], UnitConverter::UNIT_METERS, $formula);
        $this->points[2]->distanceTo($this->points[3], UnitConverter::UNIT_METERS, $formula);
        $this->points[3]->distanceTo($this->points[0], UnitConverter::UNIT_METERS, $formula);
        $this->points[3]->distanceTo($this->points[1], UnitConverter::UNIT_METERS, $formula);
        $this->points[3]->distanceTo($this->points[2], UnitConverter::UNIT_METERS, $formula);
    }

    public function runBearingTo(): void
    {
        $this->points[0]->initialBearingTo($this->points[1]);
        $this->points[0]->initialBearingTo($this->points[2]);
        $this->points[0]->initialBearingTo($this->points[3]);
        $this->points[1]->initialBearingTo($this->points[0]);
        $this->points[1]->initialBearingTo($this->points[2]);
        $this->points[1]->initialBearingTo($this->points[3]);
        $this->points[2]->initialBearingTo($this->points[0]);
        $this->points[2]->initialBearingTo($this->points[1]);
        $this->points[2]->initialBearingTo($this->points[3]);
        $this->points[3]->initialBearingTo($this->points[0]);
        $this->points[3]->initialBearingTo($this->points[1]);
        $this->points[3]->initialBearingTo($this->points[2]);
    }

    /**
     * @BeforeMethods({"enableGeospatialExtension", "initPoints"})
     */
    public function benchDistanceToHaversineWithSpatialExtension(): void
    {
        $this->runDistanceTo(new HaversineCalculator());
    }

    /**
     * @BeforeMethods({"enableGeospatialExtension", "initPoints"})
     */
    public function benchDistanceToVincentyWithSpatialExtension(): void
    {
        $this->runDistanceTo(new VincentyCalculator());
    }

    public function benchDistanceToHaversine(): void
    {
        $this->runDistanceTo(new HaversineCalculator());
    }

    public function benchDistanceEquirectangle(): void
    {
        $this->runDistanceTo(new EquirectangleCalculator());
    }

    /**
     * @BeforeMethods({"disableGeospatialExtension", "initPoints"})
     */
    public function benchDistanceToVincenty(): void
    {
        $this->runDistanceTo(new VincentyCalculator());
    }

    /**
     * @BeforeMethods({"disableGeospatialExtension", "initPoints"})
     */
    public function benchInitialBearing(): void
    {
        $this->runBearingTo();
    }

    /**
     * @BeforeMethods({"enableGeospatialExtension", "initPoints"})
     */
    public function benchInitialBearingWithSpatialExtension(): void
    {
        $this->runBearingTo();
    }
}
