<?php

declare(strict_types=1);

namespace Ricklab\Location\Transformer;

use function array_key_exists;
use function class_implements;

use ErrorException;

use function get_class;
use function in_array;

use InvalidArgumentException;

use const JSON_THROW_ON_ERROR;

use JsonException;
use Ricklab\Location\Feature\Feature;
use Ricklab\Location\Feature\FeatureCollection;
use Ricklab\Location\Geometry\BoundingBox;
use Ricklab\Location\Geometry\GeometryCollection;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\MultiLineString;
use Ricklab\Location\Geometry\MultiPoint;
use Ricklab\Location\Geometry\MultiPolygon;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\Polygon;

final class GeoJsonTransformer
{
    private const TYPE_POINT = 'Point';
    private const TYPE_LINESTRING = 'LineString';
    private const TYPE_POLYGON = 'Polygon';
    private const TYPE_MULTIPOINT = 'MultiPoint';
    private const TYPE_MULTILINESTRING = 'MultiLineString';
    private const TYPE_MULTIPOLYGON = 'MultiPolygon';
    private const TYPE_GEOMETRYCOLLECTION = 'GeometryCollection';
    private const TYPE_FEATURE = 'Feature';
    private const TYPE_FEATURECOLLECTION = 'FeatureCollection';

    private const CLASS_MAP = [
        Point::class => self::TYPE_POINT,
        LineString::class => self::TYPE_LINESTRING,
        Polygon::class => self::TYPE_POLYGON,
        MultiPoint::class => self::TYPE_MULTIPOINT,
        MultiLineString::class => self::TYPE_MULTILINESTRING,
        MultiPolygon::class => self::TYPE_MULTIPOLYGON,
        GeometryCollection::class => self::TYPE_GEOMETRYCOLLECTION,
        BoundingBox::class => self::TYPE_POLYGON,
        Feature::class => self::TYPE_FEATURE,
        FeatureCollection::class => self::TYPE_FEATURECOLLECTION,
    ];

    private const TYPE_MAP = [
        self::TYPE_POINT => Point::class,
        self::TYPE_LINESTRING => LineString::class,
        self::TYPE_POLYGON => Polygon::class,
        self::TYPE_MULTIPOINT => MultiPoint::class,
        self::TYPE_MULTILINESTRING => MultiLineString::class,
        self::TYPE_MULTIPOLYGON => MultiPolygon::class,
        self::TYPE_GEOMETRYCOLLECTION => GeometryCollection::class,
        self::TYPE_FEATURE => Feature::class,
        self::TYPE_FEATURECOLLECTION => FeatureCollection::class,
    ];

    /** @var array<string, class-string>|null */
    private static ?array $typeMap = null;

    /**
     * @throws ErrorException
     * @throws JsonException
     *
     * @return GeometryInterface|FeatureCollection|Feature
     */
    public static function decode(string $geojson)
    {
        return self::fromArray(json_decode($geojson, true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     *
     * @return GeometryInterface|FeatureCollection|Feature
     */
    public static function fromObject(object $geojson)
    {
        return self::fromArray(
            json_decode(
                json_encode($geojson, JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * Create a geometry from GeoJSON array.
     *
     * @throws ErrorException
     *
     * @return GeometryInterface|FeatureCollection|Feature
     */
    public static function fromArray(array $geojson)
    {
        $type = $geojson['type'] ?? null;

        if (null === $type) {
            throw new InvalidArgumentException('Cannot determine GeoJSON type.');
        }

        $class = self::TYPE_MAP[$type] ?? null;

        if (null === $class) {
            throw new InvalidArgumentException('GeoJSON type not supported');
        }

        switch ($class) {
            case GeometryCollection::class:
                $geometries = array_map(
                    static fn (array $geom) => self::fromArray($geom),
                    $geojson['geometries'] ?? []
                );

                return GeometryCollection::fromArray($geometries);
            case Feature::class:
                return Feature::fromGeoJson($geojson);
            case FeatureCollection::class:
                return FeatureCollection::fromGeoJson($geojson);
        }

        self::assertStringGeometryInterface($class);

        return $class::fromArray($geojson['coordinates']);
    }

    /**
     * @param GeometryInterface|Feature|FeatureCollection $object
     */
    public static function jsonArray(object $object): array
    {
        $class = get_class($object);

        if (!array_key_exists($class, self::CLASS_MAP)) {
            throw new InvalidArgumentException(sprintf('Unsupported GeoJSON type %s', get_class($object)));
        }
        $type = self::CLASS_MAP[$class];
        $result = ['type' => $type];

        if ($object instanceof GeometryInterface) {
            $content = self::arrayFromGeometry($object);
        } elseif ($object instanceof Feature) {
            $content = self::arrayFromFeature($object);
        } elseif ($object instanceof FeatureCollection) {
            $content = self::arrayFromFeatureCollection($object);
        } else {
            throw new InvalidArgumentException('Unsupported object');
        }

        return array_merge($result, $content);
    }

    /**
     * @param GeometryInterface|Feature|FeatureCollection $object
     */
    public static function encode($object): string
    {
        return json_encode(self::jsonArray($object));
    }

    private static function arrayFromGeometry(GeometryInterface $geometry): array
    {
        $result = [];

        if ($geometry instanceof GeometryCollection) {
            $result['geometries'] = array_map(
                static fn (GeometryInterface $geometry): array => self::jsonArray($geometry),
                $geometry->getGeometries()
            );

            return $result;
        }

        $result['coordinates'] = $geometry->toArray();

        return $result;
    }

    private static function arrayFromFeature(Feature $feature): array
    {
        $result = [];

        if (null !== $feature->getId()) {
            $result['id'] = $feature->getId();
        }
        $geometry = $feature->getGeometry();

        if ($geometry instanceof GeometryInterface) {
            $bbox = $feature->getBoundingBox();

            if (null !== $bbox) {
                $result['bbox'] = $bbox->getBounds();
            }
            $result['geometry'] = self::jsonArray($geometry);
        } else {
            $result['geometry'] = null;
        }

        $result['properties'] = $feature->getProperties();

        return $result;
    }

    private static function arrayFromFeatureCollection(FeatureCollection $featureCollection): array
    {
        $features = array_map(static fn (Feature $f): array => self::jsonArray($f), $featureCollection->getFeatures());

        $result = [];

        $bbox = $featureCollection->getBbox();

        if (null !== $bbox) {
            $result['bbox'] = $bbox->getBounds();
        }

        $result['features'] = $features;

        return $result;
    }

    /** @psalm-assert class-string<GeometryInterface> $geometryClass */
    private static function assertStringGeometryInterface(string $geometryClass): void
    {
        if (!in_array(GeometryInterface::class, class_implements($geometryClass))) {
            throw new InvalidArgumentException('Unsupported GeoJSON type');
        }
    }
}
