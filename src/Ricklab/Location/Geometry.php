<?php


namespace Ricklab\Location;


class Geometry
{

    public static function fromGeoJson($geojson)
    {
        if (is_string($geojson)) {
            $geojson = json_decode($geojson, true);
        }

        if (is_object($geojson)) {
            $geojson = json_decode(json_encode($geojson), true);
        }


        $type = $geojson['type'];
        $coordinates = $geojson['coordinates'];
        $geometry = self::createGeometry($type, $coordinates);

        return $geometry;


    }

    protected static function createGeometry($type, array $coordinates)
    {
        switch ($type) {
            case 'Point':
                $result = new Point($coordinates);
                break;

            case 'LineString':
                $points = array();
                foreach ($coordinates as $coordinate) {
                    $points[] = new Point($coordinate);
                }
                if (count($points) > 2) {
                    $result = new MultiPointLine($points);
                } elseif (count($points) === 2) {
                    $result = new Line($points[0], $points[1]);
                } else {
                    throw new \ErrorException('cannot parse as Line');
                }
                break;
            case 'Polygon':
                $points = array();
                foreach ($coordinates[0] as $coordinate) {
                    if (is_array($coordinate)) {
                        $points[] = new Point($coordinate);
                    }

                    $result = new Polygon($points);
                }

                break;

        }

        if (isset($result)) {
            return $result;
        } else {
            throw new \InvalidArgumentException('This type of geojson is not supported');
        }
    }

} 