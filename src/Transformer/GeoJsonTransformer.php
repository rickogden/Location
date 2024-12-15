<?php

declare(strict_types=1);

namespace Ricklab\Location\Transformer;

use function array_key_exists;
use function count;

use ErrorException;
use InvalidArgumentException;

use function is_array;
use function is_float;
use function is_int;
use function is_string;

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

use function sprintf;

/**
 * @psalm-immutable
 */
final class GeoJsonTransformer
{
    private const TYPE_GEOMETRY_POINT = 'Point';
    private const TYPE_GEOMETRY_LINESTRING = 'LineString';
    private const TYPE_GEOMETRY_POLYGON = 'Polygon';
    private const TYPE_GEOMETRY_MULTIPOINT = 'MultiPoint';
    private const TYPE_GEOMETRY_MULTILINESTRING = 'MultiLineString';
    private const TYPE_GEOMETRY_MULTIPOLYGON = 'MultiPolygon';
    private const TYPE_GEOMETRY_GEOMETRYCOLLECTION = 'GeometryCollection';
    private const TYPE_FEATURE = 'Feature';
    private const TYPE_FEATURECOLLECTION = 'FeatureCollection';

    private const CLASS_MAP = [
        Point::class => self::TYPE_GEOMETRY_POINT,
        LineString::class => self::TYPE_GEOMETRY_LINESTRING,
        Polygon::class => self::TYPE_GEOMETRY_POLYGON,
        MultiPoint::class => self::TYPE_GEOMETRY_MULTIPOINT,
        MultiLineString::class => self::TYPE_GEOMETRY_MULTILINESTRING,
        MultiPolygon::class => self::TYPE_GEOMETRY_MULTIPOLYGON,
        GeometryCollection::class => self::TYPE_GEOMETRY_GEOMETRYCOLLECTION,
        BoundingBox::class => self::TYPE_GEOMETRY_POLYGON,
        Feature::class => self::TYPE_FEATURE,
        FeatureCollection::class => self::TYPE_FEATURECOLLECTION,
    ];

    private const GEOMETRY_TYPE_MAP = [
        self::TYPE_GEOMETRY_POINT => Point::class,
        self::TYPE_GEOMETRY_LINESTRING => LineString::class,
        self::TYPE_GEOMETRY_POLYGON => Polygon::class,
        self::TYPE_GEOMETRY_MULTIPOINT => MultiPoint::class,
        self::TYPE_GEOMETRY_MULTILINESTRING => MultiLineString::class,
        self::TYPE_GEOMETRY_MULTIPOLYGON => MultiPolygon::class,
        self::TYPE_GEOMETRY_GEOMETRYCOLLECTION => GeometryCollection::class,
    ];

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public static function decode(string $geoJson): GeometryInterface|FeatureCollection|Feature
    {
        $jsonArray = json_decode($geoJson, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($jsonArray)) {
            throw new InvalidArgumentException('Not a valid GeoJSON string');
        }

        return self::fromArray($jsonArray);
    }

    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public static function fromObject(object $geoJson): GeometryInterface|FeatureCollection|Feature
    {
        $jsonArray = json_decode(
            json_encode($geoJson, JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($jsonArray)) {
            throw new InvalidArgumentException('Invalid GeoJSON object');
        }

        return self::fromArray($jsonArray);
    }

    public static function fromArray(array $geoJson): GeometryInterface|FeatureCollection|Feature
    {
        if (!isset($geoJson['type']) || !is_string($geoJson['type'])) {
            throw new InvalidArgumentException('Cannot determine GeoJSON type.');
        }

        if (self::isGeometryType($geoJson['type'])) {
            return self::geometryFromArray($geoJson);
        }

        switch ($geoJson['type']) {
            case self::TYPE_FEATURE:
                return self::featureFromArray($geoJson);
            case self::TYPE_FEATURECOLLECTION:
                return self::featureCollectionFromArray($geoJson);
        }

        throw new InvalidArgumentException('Invalid GeoJSON type');
    }

    private static function featureFromArray(array $geoJson): Feature
    {
        $decodedGeo = null;

        if (is_array($geoJson['geometry'])) {
            $decodedGeo = self::geometryFromArray($geoJson['geometry']);
        }
        $properties = $geoJson['properties'] ?? null;

        if (!is_array($properties)) {
            throw new InvalidArgumentException('Invalid GeoJSON feature');
        }

        $id = $geoJson['id'] ?? null;

        if (!(
            null === $id
            || is_string($id)
            || is_int($id)
            || is_float($id)
        )) {
            throw new InvalidArgumentException('Invalid GeoJSON feature ID');
        }

        /** @var array|null $bboxArray */
        $bboxArray = $geoJson['bbox'] ?? null;

        if (
            is_array($bboxArray)
            && self::isBoundingBoxArray($bboxArray)
        ) {
            $bbox = BoundingBox::fromArray($bboxArray);
            $feature = Feature::createWithExistingBoundingBox(
                $bbox,
                $properties,
                $decodedGeo,
                $id
            );
        } else {
            $feature = new Feature(
                $properties,
                $decodedGeo,
                $id,
                isset($geoJson['bbox'])
            );
        }

        return $feature;
    }

    /** @psalm-assert-if-true array{0: float, 1: float, 2: float, 3: float} $boundingBoxArray */
    private static function isBoundingBoxArray(array $boundingBoxArray): bool
    {
        return 4 === count($boundingBoxArray)
            && is_float($boundingBoxArray[0])
            && is_float($boundingBoxArray[1])
            && is_float($boundingBoxArray[2])
            && is_float($boundingBoxArray[3])
        ;
    }

    private static function featureCollectionFromArray(array $geoJson): FeatureCollection
    {
        if (!is_array($geoJson['features'])) {
            throw new InvalidArgumentException('Invalid geoJSON feature collection.');
        }

        $features = array_values(array_map(
            static fn (array $feature): Feature => self::featureFromArray($feature),
            $geoJson['features']
        ));

        if (
            isset($geoJson['bbox'])
            && is_array($geoJson['bbox'])
            && self::isBoundingBoxArray($geoJson['bbox'])
        ) {
            $collection = FeatureCollection::createWithExistingBoundingBox(BoundingBox::fromArray($geoJson['bbox']), $features);
        } else {
            $collection = new FeatureCollection($features, isset($geoJson['bbox']));
        }

        return $collection;
    }

    private static function geometryFromArray(array $geoJson): GeometryInterface
    {
        if (!isset($geoJson['type']) || !is_string($geoJson['type'])) {
            throw new InvalidArgumentException('Cannot determine GeoJSON type.');
        }

        $class = self::GEOMETRY_TYPE_MAP[$geoJson['type']] ?? null;

        if (null === $class) {
            throw new InvalidArgumentException('Unknown GeoJSON type '.$geoJson['type']);
        }

        if (GeometryCollection::class === $class) {
            if (!isset($geoJson['geometries']) || !is_array($geoJson['geometries'])) {
                throw new InvalidArgumentException('Geometry collection requires an array of geometries');
            }

            $geometries = array_map(static fn (array $g): GeometryInterface => self::geometryFromArray($g), $geoJson['geometries']);

            return new GeometryCollection($geometries);
        }

        if (!isset($geoJson['coordinates']) || !is_array($geoJson['coordinates'])) {
            throw new InvalidArgumentException('Geometry requires an array of coordinates');
        }

        return $class::fromArray($geoJson['coordinates']);
    }

    /**
     * @psalm-assert-if-true self::TYPE_GEOMETRY_* $type
     */
    private static function isGeometryType(string $type): bool
    {
        return isset(self::GEOMETRY_TYPE_MAP[$type]);
    }

    public static function jsonArray(GeometryInterface|FeatureCollection|Feature $object): array
    {
        $class = $object::class;

        if (!array_key_exists($class, self::CLASS_MAP)) {
            throw new InvalidArgumentException(sprintf('Unsupported GeoJSON type %s', $object::class));
        }
        $type = self::CLASS_MAP[$class];
        $result = ['type' => $type];

        if ($object instanceof GeometryInterface) {
            $content = self::arrayFromGeometry($object);
        } elseif ($object instanceof Feature) {
            $content = self::arrayFromFeature($object);
        } else {
            $content = self::arrayFromFeatureCollection($object);
        }

        return array_merge($result, $content);
    }

    public static function encode(GeometryInterface|FeatureCollection|Feature $object): string
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
}
