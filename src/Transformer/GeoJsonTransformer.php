<?php

declare(strict_types=1);

namespace Ricklab\Location\Transformer;

use Ricklab\Location\Feature\Feature;
use Ricklab\Location\Feature\FeatureCollection;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Transformer\Traits\CreateGeometryTrait;

final class GeoJsonTransformer
{
    use CreateGeometryTrait;

    /**
     * @throws \JsonException
     * @throws \ErrorException
     *
     * @return GeometryInterface|FeatureCollection|Feature
     */
    public static function decode(string $geojson)
    {
        return self::fromArray(\json_decode($geojson, true, 512, \JSON_THROW_ON_ERROR));
    }

    /**
     * @throws \JsonException
     * @throws \ErrorException
     *
     * @return GeometryInterface|FeatureCollection|Feature
     */
    public static function fromObject(object $geojson)
    {
        return self::fromArray(
            \json_decode(
                \json_encode($geojson, \JSON_THROW_ON_ERROR),
                true,
                512,
                \JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * Create a geometry from GeoJSON array.
     *
     * @throws \ErrorException
     *
     * @return GeometryInterface|FeatureCollection|Feature
     */
    public static function fromArray(array $geojson)
    {
        $type = $geojson['type'] ?? null;

        if (null === $type) {
            throw new \InvalidArgumentException('Cannot determine GeoJSON type.');
        }

        $type = \mb_strtolower($type);

        switch ($type) {
            case 'geometrycollection':
                $geometries = \array_map(
                    static fn (array $geom) => self::fromArray($geom),
                    $geojson['geometries'] ?? []
                );

                return self::createGeometry($type, $geometries);
            case 'feature':
                return Feature::fromGeoJson($geojson);
            case 'featurecollection':
                return FeatureCollection::fromGeoJson($geojson);
            default:
                return self::createGeometry($type, $geojson['coordinates']);
        }
    }

    /**
     * @param GeometryInterface|Feature|FeatureCollection $object
     */
    public static function encode($object): string
    {
        if (!$object instanceof \JsonSerializable) {
            throw new \InvalidArgumentException('Cannot serialize object.');
        }

        return \json_encode($object);
    }
}
