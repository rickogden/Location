<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 18/08/14
 * Time: 09:58.
 */

namespace Ricklab\Location;

use Ricklab\Location\Ellipsoid\Earth;
use Ricklab\Location\Ellipsoid\Ellipsoid;
use Ricklab\Location\Exception\BoundBoxRangeException;
use Ricklab\Location\Feature\Feature;
use Ricklab\Location\Feature\FeatureAbstract;
use Ricklab\Location\Feature\FeatureCollection;
use Ricklab\Location\Geometry\GeometryCollection;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\MultiLineString;
use Ricklab\Location\Geometry\MultiPoint;
use Ricklab\Location\Geometry\MultiPolygon;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\Polygon;

class Location
{
    public const FORMULA_HAVERSINE = 1;
    public const FORMULA_VINCENTY = 2;

    public const UNIT_FEET = 'feet';
    public const UNIT_KM = 'km';
    public const UNIT_METRES = 'metres';
    public const UNIT_MILES = 'miles';
    public const UNIT_NAUTICAL_MILES = 'nautical miles';
    public const UNIT_YARDS = 'yards';

    /**
     * @var bool Set to false if you have the pecl geospatial extension installed but do not want to use it
     */
    public static $useSpatialExtension = true;

    /**
     * @var int Set to either Location::HAVERSINE or Location::VICENTY. Defaults to Location::HAVERSINE
     */
    public static $defaultFormula = self::FORMULA_HAVERSINE;

    /**
     * @var Ellipsoid
     */
    protected static $ellipsoid;

    /**
     * Create a geometry from GeoJSON.
     *
     * @param string|array|object $geojson the GeoJSON object either in a JSON string or a pre-parsed array/object
     *
     * @throws \ErrorException
     *
     * @return GeometryInterface|FeatureAbstract
     */
    public static function fromGeoJson($geojson)
    {
        if (\is_string($geojson)) {
            $geojson = \json_decode($geojson, true);
        }

        if (\is_object($geojson)) {
            $geojson = \json_decode(\json_encode($geojson), true);
        }

        $type = $geojson['type'];

        if ('GeometryCollection' === $type) {
            $geometries = [];
            foreach ($geojson['geometries'] as $geom) {
                $geometries[] = self::fromGeoJson($geom);
            }

            $geometry = self::createGeometry($type, $geometries);
        } elseif ('feature' === \mb_strtolower($type)) {
            $geometry = new Feature();

            if (isset($geojson['geometry'])) {
                $geometry->setGeometry(self::fromGeoJson($geojson['geometry']));
            }

            if (isset($geojson['properties'])) {
                $geometry->setProperties($geojson['properties']);
            }
        } elseif ('featurecollection' === \mb_strtolower($type)) {
            $geometry = new FeatureCollection();

            foreach ($geojson['features'] as $feature) {
                /* @noinspection PhpParamsInspection */
                $geometry->addFeature(self::fromGeoJson($feature));
            }
        } else {
            $coordinates = $geojson['coordinates'];
            $geometry = self::createGeometry($type, $coordinates);
        }

        return $geometry;
    }

    /**
     * @param $type string the geometry type to create
     * @param $coordinates array the coordinates for the geometry type
     */
    protected static function createGeometry(string $type, array $coordinates): GeometryInterface
    {
        switch (\mb_strtolower($type)) {
            case 'point':
                $result = Point::fromArray($coordinates);
                break;
            case 'linestring':
                $result = LineString::fromArray($coordinates);
                break;
            case 'polygon':
                $result = Polygon::fromArray($coordinates);
                break;
            case 'multipoint':
                $result = MultiPoint::fromArray($coordinates);
                break;
            case 'multilinestring':
                $result = MultiLineString::fromArray($coordinates);
                break;
            case 'multipolygon':
                $result = MultiPolygon::fromArray($coordinates);
                break;
            case 'geometrycollection':
                $result = GeometryCollection::fromArray($coordinates);
                break;
            default:
                throw new \InvalidArgumentException('This type is not supported');
        }

        return $result;
    }

    /**
     * Creates a geometry object from Well-Known Text.
     *
     * @param string $wkt The WKT to create the geometry from
     */
    public static function fromWkt(string $wkt): GeometryInterface
    {
        $type = \trim(\mb_substr($wkt, 0, \mb_strpos($wkt, '(')));
        $wkt = \trim(\str_replace($type, '', $wkt));

        if ('geometrycollection' === \mb_strtolower($type)) {
            $geocol = \preg_replace('/,?\s*([A-Za-z]+\()/', ':$1', $wkt);
            $geocol = \trim($geocol);
            $geocol = \preg_replace('/^\(/', '', $geocol);
            $geocol = \preg_replace('/\)$/', '', $geocol);

            $arrays = [];
            foreach (\explode(':', $geocol) as $subwkt) {
                if ('' !== $subwkt) {
                    $arrays[] = self::fromWkt($subwkt);
                }
            }
        } else {
            $json = \str_replace([', ', ' ,', '(', ')'], [',', ',', '[', ']'], $wkt);

            if ('point' === \mb_strtolower($type)) {
                $json = \preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '$1, $2', $json);
            } else {
                $json = \preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '[$1, $2]', $json);
            }

            if (null === $json) {
                throw new \InvalidArgumentException('This is not recognised WKT.');
            }
            $arrays = \json_decode($json, true);

            if (!$arrays) {
                throw new \InvalidArgumentException('This is not recognised WKT.');
            }

            if ('multipoint' === \mb_strtolower($type)) {
                foreach ($arrays as $index => $points) {
                    if (\is_array($points[0])) {
                        $arrays[$index] = $points[0];
                    }
                }
            }
        }

        return self::createGeometry($type, $arrays);
    }

    /**
     * @param Point    $point1  distance from this point
     * @param Point    $point2  distance to this point
     * @param string   $unit    of measurement in which to return the result
     * @param int|null $formula formula to use, either Location::VINCENTY or Location::HAVERSINE. Defaults to
     *                          Location::$defaultFormula
     */
    public static function calculateDistance(
        Point $point1,
        Point $point2,
        string $unit,
        int $formula = self::FORMULA_HAVERSINE
    ): float {
        if (null === $formula) {
            $formula = self::$defaultFormula;
        }

        if (self::FORMULA_VINCENTY === $formula) {
            $mDistance = self::vincenty($point1, $point2);

            if ('m' === $unit) {
                return $mDistance;
            }

            return self::convert($mDistance, 'm', $unit);
        }
        $radDistance = self::haversine($point1, $point2);

        return $radDistance * self::getEllipsoid()->radius($unit);
    }

    /**
     * Vincenty formula for calculating distances.
     *
     *
     * @return float distance in metres
     */
    public static function vincenty(Point $point1, Point $point2): float
    {
        if (\function_exists('vincenty') && self::$useSpatialExtension && self::getEllipsoid() instanceof Earth) {
            $from = $point1->jsonSerialize();
            $to = $point2->jsonSerialize();

            return \vincenty($from, $to);
        }
        $ellipsoid = self::getEllipsoid();

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

            if (!\is_numeric($cosof2sigma)) {
                $cosof2sigma = 0;
            }
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

    /**
     * @return Earth|Ellipsoid the ellipsoid in use (generally Earth)
     */
    public static function getEllipsoid(): Ellipsoid
    {
        if (null === self::$ellipsoid) {
            self::$ellipsoid = new Earth();
        }

        return self::$ellipsoid;
    }

    /**
     * Set the ellipsoid to perform the calculations on.
     */
    public static function setEllipsoid(Ellipsoid $ellipsoid): void
    {
        self::$ellipsoid = $ellipsoid;
    }

    /**
     * Converts distances from one unit of measurement to another.
     *
     * @param $distance float the distance measurement
     * @param $from string the unit the distance measurement is in
     * @param $to string the unit the distance should be converted into
     *
     * @return float the distance in the new unit of measurement
     */
    public static function convert(float $distance, string $from, string $to): float
    {
        $ellipsoid = self::getEllipsoid();

        $m = $distance / $ellipsoid->getMultiplier($from);

        return $m * $ellipsoid->getMultiplier($to);
    }

    /**
     * Uses the haversine formula to calculate the distance between 2 points.
     *
     *
     * @return float distance in radians
     */
    public static function haversine(Point $point1, Point $point2): float
    {
        if (\function_exists('haversine') && self::$useSpatialExtension) {
            $from = $point1->jsonSerialize();
            $to = $point2->jsonSerialize();

            $radDistance = \haversine($from, $to, 1);
        } else {
            $lat1 = $point1->latitudeToRad();
            $lon1 = $point1->longitudeToRad();
            $lat2 = $point2->latitudeToRad();
            $lon2 = $point2->longitudeToRad();

            $distanceLat = $lat1 - $lat2;
            $distanceLong = $lon1 - $lon2;

            $radDistance = \sin($distanceLat / 2) * \sin($distanceLat / 2) +
                           \cos($lat1) * \cos($lat2) *
                           \sin($distanceLong / 2) * \sin($distanceLong / 2);
            $radDistance = 2 * \atan2(\sqrt($radDistance), \sqrt(1 - $radDistance));
        }

        return $radDistance;
    }

    /**
     * @param Point $point the centre of the bounding box
     * @param number $radius minimum radius from $point
     * @param string $unit unit of the radius (default is kilometres)
     *
     * @throws BoundBoxRangeException
     *
     * @return Polygon the BBox
     */
    public static function getBBoxByRadius(Point $point, float $radius, $unit = 'km'): Polygon
    {
        $north = $point->getRelativePoint($radius, 0, $unit);
        $south = $point->getRelativePoint($radius, 180, $unit);

        $limits['n'] = $north->getLatitude();
        $limits['s'] = $south->getLatitude();

        $radDist = $radius / self::getEllipsoid()->radius($unit);
        $radLon = $point->longitudeToRad();
        $deltaLon = \asin(\sin($radDist) / \cos($point->latitudeToRad()));

        if (\is_nan($deltaLon)) {
            throw new BoundBoxRangeException('Cannot create a bounding-box at these coordinates.');
        }
        $minLon = $radLon - $deltaLon;

        if ($minLon < \deg2rad(-180)) {
            $minLon += 2 * \M_PI;
        }
        $maxLon = $radLon + $deltaLon;

        if ($maxLon > \deg2rad(180)) {
            $maxLon -= 2 * \M_PI;
        }

        $limits['w'] = \rad2deg($minLon);
        $limits['e'] = \rad2deg($maxLon);

        $nw = new Point($limits['w'], $limits['n']);
        $ne = new Point($limits['e'], $limits['n']);
        $sw = new Point($limits['w'], $limits['s']);
        $se = new Point($limits['e'], $limits['s']);

        return new Polygon([new LineString([$nw, $ne, $se, $sw])]);
    }

    public static function getBBox(GeometryInterface $geometry): Polygon
    {
        [$minLon, $minLat, $maxLon, $maxLat] = self::getBBoxArray($geometry);

        $nw = Point::fromArray([$minLon, $maxLat]);
        $ne = Point::fromArray([$maxLon, $maxLat]);
        $se = Point::fromArray([$maxLon, $minLat]);
        $sw = Point::fromArray([$minLon, $minLat]);

        return Polygon::fromArray([[$nw, $ne, $se, $sw]]);
    }

    /**
     * @return array of coordinates in the order of: minimum longitude, minimum latitude, maximum longitude and maximum latitude
     */
    public static function getBBoxArray(GeometryInterface $geometry): array
    {
        $maxLat = -90;
        $minLat = 90;
        $maxLon = -180;
        $minLon = 180;

        $points = $geometry->getPoints();

        /** @var Point $point */
        foreach ($points as $point) {
            $maxLat = ($point->getLatitude() > $maxLat) ? $point->getLatitude() : $maxLat;
            $minLat = ($point->getLatitude() < $minLat) ? $point->getLatitude() : $minLat;
            $maxLon = ($point->getLongitude() > $maxLon) ? $point->getLongitude() : $maxLon;
            $minLon = ($point->getLongitude() < $minLon) ? $point->getLongitude() : $minLon;
        }

        return [$minLon, $minLat, $maxLon, $maxLat];
    }

    /**
     * @param string|null $direction use "S" for south and "W" for west. Defaults to East/North.
     */
    public static function dmsToDecimal(int $degrees, int $minutes, float $seconds, ?string $direction = null): float
    {
        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ('S' === $direction || 'W' === $direction) {
            $decimal *= -1;
        }

        return $decimal;
    }

    /**
     * @param float $decimal the decimal longitude/latitude
     *
     * @return array of degrees, minutes, seconds from North/East
     */
    public static function decimalToDms(float $decimal): array
    {
        $deg = \floor($decimal);
        $min = \floor(($decimal - $deg) * 60);
        $sec = ($decimal - $deg - $min / 60) * 3600;

        return [$deg, $min, $sec];
    }

    public static function getInitialBearing(Point $point1, Point $point2): float
    {
        if (
            self::$useSpatialExtension &&
            ($geospatialVersion = \phpversion('geospatial')) &&
            \version_compare($geospatialVersion, '0.2.2-dev', '>=')
        ) {
            return \initial_bearing($point1->jsonSerialize(), $point2->jsonSerialize());
        }
        $y = \sin(
                \deg2rad($point2->getLongitude() - $point1->getLongitude())
            ) * \cos($point2->latitudeToRad());
        $x = \cos($point1->latitudeToRad())
            * \sin($point2->latitudeToRad()) - \sin(
                $point1->latitudeToRad()
            ) * \cos($point2->latitudeToRad()) *
            \cos(
                \deg2rad($point2->getLongitude() - $point1->getLongitude())
            );
        $result = \atan2($y, $x);

        return \fmod(\rad2deg($result) + 360, 360);
    }

    public static function getFinalBearing(Point $point1, Point $point2): float
    {
        return \fmod(self::getInitialBearing($point2, $point1) + 180, 360);
    }

    public static function getFractionAlongLineBetween(Point $point1, Point $point2, float $fraction): Point
    {
        if ($fraction < 0 || $fraction > 1) {
            throw new \InvalidArgumentException('$fraction must be between 0 and 1');
        }

        if (self::$useSpatialExtension && \function_exists('fraction_along_gc_line')) {
            $result = \fraction_along_gc_line($point1->jsonSerialize(), $point2->jsonSerialize(), $fraction);

            return Point::fromArray($result['coordinates']);
        }
        $distance = self::haversine($point1, $point2);

        $lat1 = $point1->latitudeToRad();
        $lat2 = $point2->latitudeToRad();
        $lon1 = $point1->longitudeToRad();
        $lon2 = $point2->longitudeToRad();

        $a = \sin((1 - $fraction) * $distance) / \sin($distance);
        $b = \sin($fraction * $distance) / \sin($distance);
        $x = $a * \cos($lat1) * \cos($lon1) +
            $b * \cos($lat2) * \cos($lon2);
        $y = $a * \cos($lat1) * \sin($lon1) +
            $b * \cos($lat2) * \sin($lon2);
        $z = $a * \sin($lat1) + $b * \sin($lat2);
        $res_lat = \atan2($z, \sqrt(($x ** 2) + ($y ** 2)));
        $res_long = \atan2($y, $x);

        return new Point(\rad2deg($res_long), \rad2deg($res_lat));
    }
}
