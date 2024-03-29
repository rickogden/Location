<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use function function_exists;

use InvalidArgumentException;
use Ricklab\Location\Calculator\Traits\GeoSpatialExtensionTrait;
use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

final class FractionAlongLineCalculator implements UsesGeoSpatialExtensionInterface
{
    use GeoSpatialExtensionTrait;

    public static function calculate(
        Point $point1,
        Point $point2,
        float $fraction,
        DistanceCalculator $calculator,
        EllipsoidInterface $ellipsoid
    ): Point {
        if ($fraction < 0 || $fraction > 1) {
            throw new InvalidArgumentException('$fraction must be between 0 and 1');
        }

        if (
            self::$useSpatialExtension
            && function_exists('fraction_along_gc_line')
            && HaversineCalculator::FORMULA === $calculator::formula()
        ) {
            $result = fraction_along_gc_line($point1->jsonSerialize(), $point2->jsonSerialize(), $fraction);

            return Point::fromArray($result['coordinates']);
        }
        $distance = $calculator::calculate($point1, $point2, $ellipsoid) / $ellipsoid::radius();

        $lat1 = $point1->latitudeToRad();
        $lat2 = $point2->latitudeToRad();
        $lon1 = $point1->longitudeToRad();
        $lon2 = $point2->longitudeToRad();

        $a = sin((1 - $fraction) * $distance) / sin($distance);
        $b = sin($fraction * $distance) / sin($distance);
        $x = $a * cos($lat1) * cos($lon1) +
            $b * cos($lat2) * cos($lon2);
        $y = $a * cos($lat1) * sin($lon1) +
            $b * cos($lat2) * sin($lon2);
        $z = $a * sin($lat1) + $b * sin($lat2);
        $res_lat = atan2($z, sqrt(($x ** 2) + ($y ** 2)));
        $res_long = atan2($y, $x);

        return new Point(rad2deg($res_long), rad2deg($res_lat));
    }
}
