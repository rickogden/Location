<?php

declare(strict_types=1);

namespace Ricklab\Location\Transformer;

use function array_key_exists;

use InvalidArgumentException;

use function is_array;

use const JSON_THROW_ON_ERROR;

use JsonException;
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
final class WktTransformer
{
    private const TYPE_POINT = 'POINT';
    private const TYPE_LINESTRING = 'LINESTRING';
    private const TYPE_POLYGON = 'POLYGON';
    private const TYPE_MULTIPOINT = 'MULTIPOINT';
    private const TYPE_MULTILINESTRING = 'MULTILINESTRING';
    private const TYPE_MULTIPOLYGON = 'MULTIPOLYGON';
    private const TYPE_GEOMETRYCOLLECTION = 'GEOMETRYCOLLECTION';

    /** @var array<class-string<GeometryInterface>, string> */
    private const CLASS_MAP = [
        Point::class => self::TYPE_POINT,
        LineString::class => self::TYPE_LINESTRING,
        Polygon::class => self::TYPE_POLYGON,
        MultiPoint::class => self::TYPE_MULTIPOINT,
        MultiLineString::class => self::TYPE_MULTILINESTRING,
        MultiPolygon::class => self::TYPE_MULTIPOLYGON,
        GeometryCollection::class => self::TYPE_GEOMETRYCOLLECTION,
        BoundingBox::class => self::TYPE_POLYGON,
    ];

    /** @var array<string, class-string<GeometryInterface>> */
    private const TYPE_MAP = [
        self::TYPE_POINT => Point::class,
        self::TYPE_LINESTRING => LineString::class,
        self::TYPE_POLYGON => Polygon::class,
        self::TYPE_MULTIPOINT => MultiPoint::class,
        self::TYPE_MULTILINESTRING => MultiLineString::class,
        self::TYPE_MULTIPOLYGON => MultiPolygon::class,
        self::TYPE_GEOMETRYCOLLECTION => GeometryCollection::class,
    ];

    public static function decode(string $wkt): GeometryInterface
    {
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        $type = mb_strtoupper(mb_trim(mb_substr($wkt, 0, mb_strpos($wkt, '(') ?: 0)));
        $wkt = mb_trim(str_replace($type, '', $wkt));

        if (self::TYPE_GEOMETRYCOLLECTION === $type) {
            $geocol = preg_replace('/,?\s*([A-Za-z]+\()/', ':$1', $wkt) ?? '';
            $geocol = mb_trim($geocol);
            $geocol = preg_replace('/^\(/', '', $geocol) ?? '';
            $geocol = preg_replace('/\)$/', '', $geocol) ?? '';

            $arrays = [];
            foreach (explode(':', $geocol) as $subwkt) {
                if ('' !== $subwkt) {
                    $arrays[] = self::decode($subwkt);
                }
            }
        } else {
            $json = str_replace([', ', ' ,', '(', ')'], [',', ',', '[', ']'], $wkt);

            if (self::TYPE_POINT === $type) {
                $json = preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '$1, $2', $json);
            } else {
                $json = preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '[$1, $2]', $json);
            }

            if (null === $json) {
                throw new InvalidArgumentException('This is not recognized WKT.');
            }

            try {
                /** @var array|null $arrays */
                $arrays = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new InvalidArgumentException('This is not recognized WKT.', 0, $e);
            }

            if (!is_array($arrays)) {
                throw new InvalidArgumentException('This is not recognized WKT.');
            }

            if (self::TYPE_MULTIPOINT === $type) {
                foreach ($arrays as $index => $points) {
                    if (!is_array($points)) {
                        throw new InvalidArgumentException('Not a valid WKT format');
                    }

                    if (is_array($points[0])) {
                        $arrays[$index] = $points[0];
                    }
                }
            }
        }

        $class = self::getClassFromType($type);

        return $class::fromArray($arrays);
    }

    public static function encode(GeometryInterface $geometry): string
    {
        $class = $geometry::class;

        if (!array_key_exists($class, self::CLASS_MAP)) {
            throw new InvalidArgumentException('Cannot handle geometry');
        }

        $type = self::CLASS_MAP[$class];

        if ($geometry instanceof Point) {
            return sprintf('%s(%s)', $type, $geometry->wktFormat());
        }

        return $type.$geometry->wktFormat();
    }

    /**
     * @return class-string<GeometryInterface>
     */
    private static function getClassFromType(string $type): string
    {
        $class = self::TYPE_MAP[$type] ?? null;

        if (null === $class) {
            throw new InvalidArgumentException('Unsupported WKT type');
        }

        return $class;
    }
}
