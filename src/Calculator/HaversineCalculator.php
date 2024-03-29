<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use function function_exists;

use Ricklab\Location\Calculator\Traits\GeoSpatialExtensionTrait;
use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

final class HaversineCalculator implements DistanceCalculator, UsesGeoSpatialExtensionInterface
{
    use GeoSpatialExtensionTrait;

    public const FORMULA = 'HAVERSINE';

    public static function calculate(Point $point1, Point $point2, EllipsoidInterface $ellipsoid): float
    {
        if (self::$useSpatialExtension && function_exists('haversine')) {
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

            $radDistance = sin($distanceLat / 2) * sin($distanceLat / 2) +
                cos($lat1) * cos($lat2) *
                sin($distanceLong / 2) * sin($distanceLong / 2);
            $radDistance = 2 * atan2(sqrt($radDistance), sqrt(1 - $radDistance));
        }

        return $radDistance * $ellipsoid::radius();
    }

    public static function formula(): string
    {
        return self::FORMULA;
    }
}
