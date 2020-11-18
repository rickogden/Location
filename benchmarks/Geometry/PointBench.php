<?php

declare(strict_types=1);

use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Location;

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
        Location::$useSpatialExtension = true;
    }


    /**
     * @BeforeMethods({"disableGeospatialExtension", "initPoints"})
     */
    public function disableGeospatialExtension(): void
    {
        Location::$useSpatialExtension = false;
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

    public function runDistanceTo(int $formula = Location::FORMULA_HAVERSINE): void
    {
        $this->points[0]->distanceTo($this->points[1], Location::UNIT_METRES, $formula);
        $this->points[0]->distanceTo($this->points[2], Location::UNIT_METRES, $formula);
        $this->points[0]->distanceTo($this->points[3], Location::UNIT_METRES, $formula);
        $this->points[1]->distanceTo($this->points[0], Location::UNIT_METRES, $formula);
        $this->points[1]->distanceTo($this->points[2], Location::UNIT_METRES, $formula);
        $this->points[1]->distanceTo($this->points[3], Location::UNIT_METRES, $formula);
        $this->points[2]->distanceTo($this->points[0], Location::UNIT_METRES, $formula);
        $this->points[2]->distanceTo($this->points[1], Location::UNIT_METRES, $formula);
        $this->points[2]->distanceTo($this->points[3], Location::UNIT_METRES, $formula);
        $this->points[3]->distanceTo($this->points[0], Location::UNIT_METRES, $formula);
        $this->points[3]->distanceTo($this->points[1], Location::UNIT_METRES, $formula);
        $this->points[3]->distanceTo($this->points[2], Location::UNIT_METRES, $formula);
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
        $this->runDistanceTo();
    }

    /**
     * @BeforeMethods({"enableGeospatialExtension", "initPoints"})
     */
    public function benchDistanceToVincentyWithSpatialExtension(): void
    {
        $this->runDistanceTo(Location::FORMULA_VINCENTY);
    }

    public function benchDistanceToHaversine(): void
    {
        $this->runDistanceTo();
    }


    /**
     * @BeforeMethods({"disableGeospatialExtension", "initPoints"})
     */
    public function benchDistanceToVincenty(): void
    {
        $this->runDistanceTo(Location::FORMULA_VINCENTY);
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
