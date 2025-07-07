<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use function function_exists;

use Override;
use Ricklab\Location\Calculator\Traits\GeoSpatialExtensionTrait;
use Ricklab\Location\Converter\Unit;
use Ricklab\Location\Converter\UnitConverter;
use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

final class DefaultBearingCalculator implements BearingCalculator, UsesGeoSpatialExtensionInterface
{
    use GeoSpatialExtensionTrait;

    public function __construct(
        private UnitConverter $unitConverter,
        private bool $useSpatialExtension = true,
    ) {
    }

    #[Override]
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

        return fmod(rad2deg($result) + 360.0, 360);
    }

    #[Override]
    public function calculateFinalBearing(Point $point1, Point $point2): float
    {
        return fmod($this->calculateInitialBearing($point2, $point1) + 180.0, 360);
    }

    #[Override]
    public function calculateRelativePoint(
        EllipsoidInterface $ellipsoid,
        Point $point,
        float|string $distance,
        float|string $bearing,
        Unit $unit = Unit::METERS,
    ): Point {
        $rad = (float) $this->unitConverter->convertFromMeters($ellipsoid->radius(), $unit);
        $lat1 = $point->latitudeToRad();
        $lon1 = $point->longitudeToRad();
        $bearing = deg2rad((float) $bearing);
        $distance = (float) $distance;

        $lat2 = sin($lat1) * cos($distance / $rad) +
            cos($lat1) * sin($distance / $rad) * cos($bearing);
        $lat2 = asin($lat2);

        $lon2y = sin($bearing) * sin($distance / $rad) * cos($lat1);
        $lon2x = cos($distance / $rad) - sin($lat1) * sin($lat2);
        $lon2 = $lon1 + atan2($lon2y, $lon2x);

        return new Point(rad2deg($lon2), rad2deg($lat2));
    }
}
