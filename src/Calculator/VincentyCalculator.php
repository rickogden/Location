<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use function function_exists;

use Override;
use Ricklab\Location\Calculator\Traits\GeoSpatialExtensionTrait;
use Ricklab\Location\Ellipsoid\Earth;
use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;

final class VincentyCalculator implements DistanceCalculator, UsesGeoSpatialExtensionInterface
{
    use GeoSpatialExtensionTrait;

    public const FORMULA = 'VINCENTY';

    #[Override]
    public function calculateDistance(Point $point1, Point $point2, EllipsoidInterface $ellipsoid): float
    {
        if ($this->useSpatialExtension && function_exists('vincenty') && (
            $ellipsoid instanceof Earth || $ellipsoid->equals(new Earth())
        )) {
            $from = $point1->jsonSerialize();
            $to = $point2->jsonSerialize();

            return vincenty($from, $to);
        }

        $flattening = (float) $ellipsoid->flattening();
        $majorSemiAxis = (float) $ellipsoid->majorSemiAxis();
        $minorSemiAxis = (float) $ellipsoid->minorSemiAxis();
        $U1 = atan((1.0 - $flattening) * tan($point1->latitudeToRad()));
        $U2 = atan((1.0 - $flattening) * tan($point2->latitudeToRad()));
        $L = $point2->longitudeToRad() - $point1->longitudeToRad();
        $sinU1 = sin($U1);
        $cosU1 = cos($U1);
        $sinU2 = sin($U2);
        $cosU2 = cos($U2);
        $lambda = $L;
        $loopLimit = 100;

        do {
            $sinLambda = sin($lambda);
            $cosLambda = cos($lambda);
            $sinSigma = sqrt((($cosU2 * $sinLambda) ** 2.0) +
                (($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda) ** 2.0));
            $cosSigma = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
            $sigma = atan2($sinSigma, $cosSigma);
            $sinAlpha = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
            $cos2Alpha = 1.0 - ($sinAlpha ** 2.0);
            $cosOf2Sigma = $cosSigma - 2.0 * $sinU1 * $sinU2 / $cos2Alpha;

            $C = $flattening / 16.0 * $cos2Alpha *
                (4.0 + $flattening * (4.0 - 3.0 * $cos2Alpha));
            $lambdaP = $lambda;
            $lambda = $L + (1.0 - $C) * $flattening * $sinAlpha *
                ($sigma + $C * $sinSigma * ($cosOf2Sigma + $C * $cosSigma * (-1.0 + 2.0 * ($cosOf2Sigma ** 2.0))));
        } while (abs($lambda - $lambdaP) > 1e-12 && --$loopLimit > 0);

        $uSq = $cos2Alpha * (($majorSemiAxis ** 2.0) - ($minorSemiAxis ** 2.0)) / ($minorSemiAxis ** 2.0);
        $A = 1.0 + $uSq / 16384.0 * (4096.0 + $uSq * (-768.0 + $uSq * (320.0 - 175.0 * $uSq)));
        $B = $uSq / 1024.0 * (256.0 + $uSq * (-128.0 + $uSq * (74.0 - 47.0 * $uSq)));
        $deltaSigma = $B * $sinSigma * ($cosOf2Sigma + $B / 4.0 * ($cosSigma * (-1.0 + 2.0 * ($cosOf2Sigma ** 2.0)) -
                    $B / 6.0 * $cosOf2Sigma * (-3.0 + 4.0 * ($sinSigma ** 2.0))
                    * (-3.0 + 4.0 * ($cosOf2Sigma ** 2.0))));

        return $minorSemiAxis * $A * ($sigma - $deltaSigma);
    }

    #[Override]
    public function formula(): string
    {
        return self::FORMULA;
    }
}
