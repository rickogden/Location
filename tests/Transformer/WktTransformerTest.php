<?php

declare(strict_types=1);

namespace Ricklab\Location\Transformer;

use Generator;

use function get_class;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\MultiLineString;
use Ricklab\Location\Geometry\MultiPolygon;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\Polygon;

class WktTransformerTest extends TestCase
{
    public static function geometryProvider(): Generator
    {
        yield 'MultiPolygon' => [
            'MULTIPOLYGON(((1.432 -1.543, 5 1, 5 5, 1 5, 1.432 -1.543), (2 2, 3 2, 3 3, 2 3, 2 2)), ((3 3, 6 2, 6 4, 3 3)))',
            new MultiPolygon([
                new Polygon([
                    new LineString([
                        new Point(1.432, -1.543),
                        new Point(5, 1),
                        new Point(5, 5),
                        new Point(1, 5),
                        new Point(1.432, -1.543),
                    ]),
                    new LineString([
                        new Point(2, 2),
                        new Point(3, 2),
                        new Point(3, 3),
                        new Point(2, 3),
                        new Point(2, 2),
                    ]),
                ]),
                new Polygon([
                    new LineString([
                        new Point(3, 3),
                        new Point(6, 2),
                        new Point(6, 4),
                        new Point(3, 3),
                    ]),
                ]),
            ]),
        ];

        yield 'MultiLineString' => [
            'MULTILINESTRING((3 4, 10 50, 20 25), (-5 -8, -10 -8, -15 -4))',
            new MultiLineString([
                new LineString([
                    new Point(3, 4),
                    new Point(10, 50),
                    new Point(20, 25),
                ]),
                new LineString([
                    new Point(-5, -8),
                    new Point(-10, -8),
                    new Point(-15, -4),
                ]),
            ]),
        ];

        yield 'Point' => [
            'POINT(4 5)',
            new Point(4, 5),
        ];

        yield 'LineString' => [
            'LINESTRING(3.3233 -34.1222, 5.232 -22.2332)',
            new LineString([
                new Point(3.3233, -34.1222),
                new Point(5.232, -22.2332),
            ]),
        ];
    }

    /**
     * @dataProvider geometryProvider
     */
    public function testDecode(string $wkt, GeometryInterface $geometry): void
    {
        $decoded = WktTransformer::decode($wkt);
        $this->assertInstanceOf(get_class($geometry), $decoded);
        $this->assertTrue(
            $geometry->equals($decoded),
            sprintf(
                "Geometries don\'t match:\n Expected: %s \n Actual: %s",
                json_encode($geometry),
                json_encode($decoded)
            )
        );
    }

    /**
     * @dataProvider geometryProvider
     */
    public function testEncode(string $wkt, GeometryInterface $geometry): void
    {
        $encoded = WktTransformer::encode($geometry);
        $this->assertSame($wkt, $encoded);
    }
}
