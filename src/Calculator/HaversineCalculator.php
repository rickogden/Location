<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use function function_exists;

use Override;
use Ricklab\Location\Calculator\Traits\GeoSpatialExtensionTrait;
use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

final class HaversineCalculator implements DistanceCalculator, UsesGeoSpatialExtensionInterface
{
    use GeoSpatialExtensionTrait;

    public const FORMULA = 'HAVERSINE';

    #[Override]
    public function calculateDistance(Point $point1, Point $point2, EllipsoidInterface $ellipsoid): float
    {
        if ($this->useSpatialExtension && function_exists('haversine')) {
            $from = $point1->jsonSerialize();
            $to = $point2->jsonSerialize();

            $radDistance = haversine($from, $to, 1);
        } else {
            $lat1 = $point1->latitudeToRad();
            $lon1 = $point1->longitudeToRad();
            $lat2 = $point2->latitudeToRad();
            $lon2 = $point2->longitudeToRad();

            $distanceLat = $lat1 - $lat2;
            $distanceLong = $lon1 - $lon2;

            $radDistance = sin($distanceLat / 2.0) * sin($distanceLat / 2.0) +
                cos($lat1) * cos($lat2) *
                sin($distanceLong / 2.0) * sin($distanceLong / 2.0);
            $radDistance = 2.0 * atan2(sqrt($radDistance), sqrt(1.0 - $radDistance));
        }

        return $radDistance * (float) $ellipsoid->radius();
    }

    #[Override]
    public function formula(): string
    {
        return self::FORMULA;
    }
}
