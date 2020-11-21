<?php

declare(strict_types=1);

namespace Ricklab\Location\Calculator;

use Ricklab\Location\Ellipsoid\Earth;
use Ricklab\Location\Ellipsoid\EllipsoidInterface;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Location;

final class VincentyCalculator implements DistanceCalculator
{
    public const FORMULA = 'VINCENTY';

    public static function calculate(Point $point1, Point $point2, EllipsoidInterface $ellipsoid): float
    {
        if (\function_exists('vincenty') && Location::$useSpatialExtension && $ellipsoid instanceof Earth) {
            $from = $point1->jsonSerialize();
            $to = $point2->jsonSerialize();

            return \vincenty($from, $to);
        }

        $flattening = $ellipsoid->getFlattening();
        $U1 = \atan((1.0 - $flattening) * \tan($point1->latitudeToRad()));
        $U2 = \atan((1.0 - $flattening) * \tan($point2->latitudeToRad()));
        $L = $point2->longitudeToRad() - $point1->longitudeToRad();
        $sinU1 = \sin($U1);
        $cosU1 = \cos($U1);
        $sinU2 = \sin($U2);
        $cosU2 = \cos($U2);
        $lambda = $L;
        $looplimit = 100;

        do {
            $sinLambda = \sin($lambda);
            $cosLambda = \cos($lambda);
            $sinSigma = \sqrt((($cosU2 * $sinLambda) ** 2) +
                (($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda) ** 2));
            $cosSigma = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
            $sigma = \atan2($sinSigma, $cosSigma);
            $sinAlpha = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
            $cos2Alpha = 1 - ($sinAlpha ** 2);
            $cosof2sigma = $cosSigma - 2 * $sinU1 * $sinU2 / $cos2Alpha;

            $C = $flattening / 16 * $cos2Alpha *
                (4 + $flattening * (4 - 3 * $cos2Alpha));
            $lambdaP = $lambda;
            $lambda = $L + (1 - $C) * $flattening * $sinAlpha *
                ($sigma + $C * $sinSigma * ($cosof2sigma + $C * $cosSigma * (-1 + 2 * ($cosof2sigma ** 2))));
        } while (\abs($lambda - $lambdaP) > 1e-12 && --$looplimit > 0);

        $uSq = $cos2Alpha * (($ellipsoid->getMajorSemiAxis() ** 2) - ($ellipsoid->getMinorSemiAxis() ** 2)) / ($ellipsoid->getMinorSemiAxis() ** 2);
        $A = 1 + $uSq / 16384 * (4096 + $uSq * (-768 + $uSq * (320 - 175 * $uSq)));
        $B = $uSq / 1024 * (256 + $uSq * (-128 + $uSq * (74 - 47 * $uSq)));
        $deltaSigma = $B * $sinSigma * ($cosof2sigma + $B / 4 * ($cosSigma * (-1 + 2 * ($cosof2sigma ** 2)) -
                    $B / 6 * $cosof2sigma * (-3 + 4 * ($sinSigma ** 2))
                    * (-3 + 4 * ($cosof2sigma ** 2))));
        $s = $ellipsoid->getMinorSemiAxis() * $A * ($sigma - $deltaSigma);

        return $s * 1000;
    }

    public static function formula(): string
    {
        return self::FORMULA;
    }
}
