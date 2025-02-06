<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use function function_exists;

use Ricklab\Location\Calculator\Traits\GeoSpatialExtensionTrait;
use Ricklab\Location\Geometry\Point;

final class DefaultBearingCalculator implements BearingCalculator, UsesGeoSpatialExtensionInterface
{
    use GeoSpatialExtensionTrait;

    public function calculateInitialBearing(Point $point1, Point $point2): float
    {
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (
            $this->useSpatialExtension
            && ($geospatialVersion = phpversion('geospatial'))
            && version_compare($geospatialVersion, '0.2.2-dev', '>=')
            && function_exists('initial_bearing')
        ) {
            return initial_bearing($point1->jsonSerialize(), $point2->jsonSerialize());
        }
        $y = sin(
            deg2rad($point2->getLongitude() - $point1->getLongitude())
        ) * cos($point2->latitudeToRad());
        $x = cos($point1->latitudeToRad())
            * sin($point2->latitudeToRad()) - sin(
                $point1->latitudeToRad()
            ) * cos($point2->latitudeToRad()) *
            cos(
                deg2rad($point2->getLongitude() - $point1->getLongitude())
            );
        $result = atan2($y, $x);

        return fmod(rad2deg($result) + 360, 360);
    }

    public function calculateFinalBearing(Point $point1, Point $point2): float
    {
        return fmod($this->calculateInitialBearing($point2, $point1) + 180, 360);
    }
}
