<?php

declare(strict_types=1);

namespace Ricklab\Location\Transformer;

use function count;

use InvalidArgumentException;
use Ricklab\Location\Geometry\GeometryCollection;
use Ricklab\Location\Geometry\GeometryCollectionInterface;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\MultiLineString;
use Ricklab\Location\Geometry\MultiPoint;
use Ricklab\Location\Geometry\MultiPolygon;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\Polygon;

class WkbTransformer
{
    private const WKB_POINT = 1;
    private const WKB_LINESTRING = 2;
    private const WKB_POLYGON = 3;
    private const WKB_MULTIPOINT = 4;
    private const WKB_MULTILINESTRING = 5;
    private const WKB_MULTIPOLYGON = 6;
    private const WKB_GEOMETRYCOLLECTION = 7;

    private const WKB_POINT_SIZE = 16;

    private const WKB_MAP = [
        self::WKB_POINT => Point::class,
        self::WKB_LINESTRING => LineString::class,
        self::WKB_POLYGON => Polygon::class,
        self::WKB_MULTIPOINT => MultiPoint::class,
        self::WKB_MULTILINESTRING => MultiLineString::class,
        self::WKB_MULTIPOLYGON => MultiPolygon::class,
        self::WKB_GEOMETRYCOLLECTION => GeometryCollection::class,
    ];

    /** @var array<class-string<GeometryInterface>, array{class: class-string<GeometryInterface>, child_type: class-string<GeometryInterface>|null}> */
    private const GEOMETRY_DATA = [
        Point::class => [
            'class' => Point::class,
            'child_type' => null,
        ],
        LineString::class => [
            'class' => LineString::class,
            'child_type' => Point::class,
        ],
        Polygon::class => [
            'class' => Polygon::class,
            'child_type' => LineString::class,
        ],
        MultiPoint::class => [
            'class' => MultiPoint::class,
            'child_type' => null,
        ],
        MultiLineString::class => [
            'class' => MultiLineString::class,
            'child_type' => null,
        ],
        MultiPolygon::class => [
            'class' => MultiPolygon::class,
            'child_type' => null,
        ],
        GeometryCollection::class => [
            'class' => GeometryCollection::class,
            'child_type' => null,
        ],
    ];

    /** @var array<string,int> */
    private const GEOMETRY_MAP = [
        Point::class => self::WKB_POINT,
        LineString::class => self::WKB_LINESTRING,
        Polygon::class => self::WKB_POLYGON,
        MultiPoint::class => self::WKB_MULTIPOINT,
        MultiLineString::class => self::WKB_MULTILINESTRING,
        MultiPolygon::class => self::WKB_MULTIPOLYGON,
        GeometryCollection::class => self::WKB_GEOMETRYCOLLECTION,
    ];

    public static function encode(GeometryInterface $geometry): string
    {
        return self::packWkb($geometry);
    }

    public static function decode(string $wkb): GeometryInterface
    {
        return self::unpackWkb($wkb);
    }

    private static function packWkb(GeometryInterface $geometry, bool $includeType = true): string
    {
        $wkb = '';

        if ($includeType) {
            /**
             * @psalm-suppress InvalidArrayOffset
             *
             * @var int|null $wkbType
             */
            $wkbType = self::GEOMETRY_MAP[$geometry::class] ?? null;

            if (null === $wkbType) {
                throw new InvalidArgumentException('Unknown geometry type');
            }

            // Set endianness (1 for little endian)
            $wkb = pack('C', 1);
            $wkb .= pack('L', $wkbType);
        }

        if ($geometry instanceof Point) {
            return $wkb.pack('dd', $geometry->getLongitude(), $geometry->getLatitude());
        }
        $children = $geometry->getChildren();
        $wkb .= pack('L', count($children));
        foreach ($children as $child) {
            if ($geometry instanceof GeometryCollectionInterface) {
                $wkb .= self::packWkb($child);
            } else {
                $wkb .= self::packWkb($child, false);
            }
        }

        return $wkb;
    }

    private static function unpackWkb(string &$wkb, ?string $type = null): GeometryInterface
    {
        if (null === $type) {
            $endian = (int) self::unpack('C', $wkb[0])[1];
            $format = (1 === $endian) ? 'L' : 'N';
            $wkb = substr($wkb, 1);
            $typeInt = (int) self::unpack($format, $wkb)[1];
            $type = self::WKB_MAP[$typeInt] ?? null;

            if (null === $type) {
                throw new InvalidArgumentException('Unknown geometry type');
            }

            $wkb = substr($wkb, 4);
        }

        if (Point::class === $type) {
            /** @var array{1: mixed, 2: mixed} $latlon */
            $latlon = self::unpack('d2', $wkb);
            $wkb = substr($wkb, 16);

            return new Point((float) $latlon[1], (float) $latlon[2]);
        }

        $childrenCount = (int) self::unpack('L', $wkb)[1];
        $wkb = substr($wkb, 4);

        $children = [];
        $typeData = self::GEOMETRY_DATA[$type];

        for ($i = 0; $i < $childrenCount; ++$i) {
            $child = self::unpackWkb($wkb, $typeData['child_type']);
            $children[] = $child;
        }

        switch ($type) {
            case LineString::class:
                self::assertContainsGeometries($children, Point::class);

                return new LineString($children);
            case Polygon::class:
                self::assertContainsGeometries($children, LineString::class);

                return new Polygon($children);
            case MultiPoint::class:
                self::assertContainsGeometries($children, Point::class);

                return new MultiPoint($children);
            case MultiLineString::class:
                self::assertContainsGeometries($children, LineString::class);

                return new MultiLineString($children);
            case MultiPolygon::class:
                self::assertContainsGeometries($children, Polygon::class);

                return new MultiPolygon($children);
            case GeometryCollection::class:
                return new GeometryCollection($children);
        }

        throw new InvalidArgumentException('Unknown geometry type');
    }

    private static function unpack(string $format, string $string, int $offset = 0): array
    {
        $result = unpack($format, $string, $offset);

        if (false === $result) {
            throw new InvalidArgumentException('Cannot unpack WKB');
        }

        return $result;
    }

    /**
     * @template T
     *
     * @param GeometryInterface[] $geometries
     * @param class-string<T>     $type
     *
     * @psalm-assert T[] $geometries
     */
    private static function assertContainsGeometries(array $geometries, string $type): void
    {
        foreach ($geometries as $geometry) {
            if (!$geometry instanceof $type) {
                throw new InvalidArgumentException('Invalid geometry type');
            }
        }
    }
}
