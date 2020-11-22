<?php

declare(strict_types=1);

namespace Ricklab\Location\Factory\Traits;

use Ricklab\Location\Geometry\GeometryCollection;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\MultiLineString;
use Ricklab\Location\Geometry\MultiPoint;
use Ricklab\Location\Geometry\MultiPolygon;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\Polygon;

trait CreateGeometryTrait
{
    /**
     * @param $type string the geometry type to create
     * @param $coordinates array the coordinates for the geometry type
     */
    private static function createGeometry(string $type, array $coordinates): GeometryInterface
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
}
