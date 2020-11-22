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
     * @throws \JsonException
     * @throws \ErrorException
     *
     * @return GeometryInterface|FeatureCollection|Feature
     */
    public static function fromString(string $geojson)
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

        if ('geometrycollection' === $type) {
            $geometries = [];
            foreach ($geojson['geometries'] as $geom) {
                $geometries[] = self::fromArray($geom);
            }

            $geometry = self::createGeometry($type, $geometries);
        } elseif ('feature' === $type) {
            $geometry = new Feature();

            if (isset($geojson['geometry'])) {
                $decodedGeo = self::fromArray($geojson['geometry']);

                if ($decodedGeo instanceof GeometryInterface) {
                    $geometry->setGeometry($decodedGeo);
                }
            }

            if (isset($geojson['properties'])) {
                $geometry->setProperties($geojson['properties']);
            }
        } elseif ('featurecollection' === $type) {
            $geometry = new FeatureCollection();

            foreach ($geojson['features'] as $feature) {
                $decodedFeature = self::fromArray($feature);

                if ($decodedFeature instanceof Feature) {
                    $geometry->addFeature($decodedFeature);
                }
            }
        } else {
            $coordinates = $geojson['coordinates'];
            $geometry = self::createGeometry($type, $coordinates);
        }

        return $geometry;
    }
}
