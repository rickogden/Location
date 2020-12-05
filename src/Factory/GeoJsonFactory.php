<?php

declare(strict_types=1);

namespace Ricklab\Location\Factory;

use Ricklab\Location\Factory\Traits\CreateGeometryTrait;
use Ricklab\Location\Feature\Feature;
use Ricklab\Location\Feature\FeatureCollection;
use Ricklab\Location\Geometry\GeometryInterface;

final class GeoJsonFactory
{
    use CreateGeometryTrait;

    /**
     * @throws \ErrorException
     * @throws \JsonException
     *
     * @return GeometryInterface|FeatureCollection|Feature
     */
    public static function fromString(string $geojson)
    {
        return self::fromArray(\json_decode($geojson, true, 512, \JSON_THROW_ON_ERROR));
    }

    /**
     * @throws \ErrorException
     * @throws \JsonException
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

        if ('geometrycollection' === $type) {
            $geometries = [];
            foreach ($geojson['geometries'] as $geom) {
                $geometries[] = self::fromArray($geom);
            }

            $geometry = self::createGeometry($type, $geometries);
        } elseif ('feature' === $type) {
            $geometry = Feature::fromGeoJson($geojson);
        } elseif ('featurecollection' === $type) {
            $geometry = FeatureCollection::fromGeoJson($geojson);
        } else {
            $geometry = self::decodeGeometry($type, $geojson);
        }

        return $geometry;
    }

    private static function decodeGeometry($type, array $geojson): GeometryInterface
    {
        $coordinates = $geojson['coordinates'];

        return self::createGeometry($type, $coordinates);
    }
}
