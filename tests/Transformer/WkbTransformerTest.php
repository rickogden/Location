<?php

declare(strict_types=1);

namespace Ricklab\Location\Transformer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ricklab\Location\Geometry\GeometryCollection;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Geometry\LineString;
use Ricklab\Location\Geometry\MultiLineString;
use Ricklab\Location\Geometry\MultiPoint;
use Ricklab\Location\Geometry\MultiPolygon;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\Polygon;

class WkbTransformerTest extends TestCase
{
    public static function geometryProvider(): iterable
    {
        $point = new Point(-2.27354, 53.48575);

        $point1 = new Point(-2.27354, 53.48575);
        $point2 = new Point(-2.23194, 53.48204);
        $line = new LineString([$point1, $point2]);

        $polygon = Polygon::fromArray([[new Point(3, 2), new Point(4, 2), new Point(4, 3)]]);

        $multipoint = MultiPoint::fromArray([[-2.27354, 53.48575], [-2.23194, 53.48204]]);

        $lineString1 = new LineString([Point::fromArray([100.0, 0.0]), Point::fromArray([101.0, 1.0])]);
        $lineString2 = new LineString([Point::fromArray([102.0, 2.0]), Point::fromArray([103.0, 3.0])]);
        $multiLineString = new MultiLineString([$lineString1, $lineString2]);

        $polygon1 = new Polygon([
            new LineString([
                Point::fromArray([30, 20]),
                Point::fromArray([45, 40]),
                Point::fromArray([10, 40]),
                Point::fromArray([10, 40]),
                Point::fromArray([30, 20]),
            ]),
        ]);
        $polygon2 = new Polygon([
            new LineString([
                Point::fromArray([15, 5]),
                Point::fromArray([40, 10]),
                Point::fromArray([10, 20]),
                Point::fromArray([5, 10]),
                Point::fromArray([15, 5]),
            ]),
        ]);
        $multiPolygon = new MultiPolygon([$polygon1, $polygon2]);

        $geometryCollection = new GeometryCollection([
            $point,
            $line,
            $polygon,
            $multipoint,
            $multiLineString,
            $multiPolygon,
        ]);
        yield 'GeometryCollection' => [
            $geometryCollection,
            '0107000000060000000101000000c72e51bd353002c01904560e2dbe4a40010200000002000000c72e51bd353002c01904560e2dbe4a40ec12d55b03db01c092ae997cb3bd4a4001030000000100000004000000000000000000084000000000000000400000000000001040000000000000004000000000000010400000000000000840000000000000084000000000000000400104000000020000000101000000c72e51bd353002c01904560e2dbe4a400101000000ec12d55b03db01c092ae997cb3bd4a40010500000002000000010200000002000000000000000000594000000000000000000000000000405940000000000000f03f010200000002000000000000000080594000000000000000400000000000c059400000000000000840010600000002000000010300000001000000050000000000000000003e4000000000000034400000000000804640000000000000444000000000000024400000000000004440000000000000244000000000000044400000000000003e400000000000003440010300000001000000050000000000000000002e4000000000000014400000000000004440000000000000244000000000000024400000000000003440000000000000144000000000000024400000000000002e400000000000001440',
        ];
        yield 'LineString' => [
            $line,
            '010200000002000000c72e51bd353002c01904560e2dbe4a40ec12d55b03db01c092ae997cb3bd4a40',
        ];
        yield 'MultiLineString' => [
            $multiLineString,
            '010500000002000000010200000002000000000000000000594000000000000000000000000000405940000000000000f03f010200000002000000000000000080594000000000000000400000000000c059400000000000000840',
        ];
        yield 'MultiPoint' => [
            $multipoint,
            '0104000000020000000101000000c72e51bd353002c01904560e2dbe4a400101000000ec12d55b03db01c092ae997cb3bd4a40',
        ];
        yield 'MultiPolygon' => [
            $multiPolygon,
            '010600000002000000010300000001000000050000000000000000003e4000000000000034400000000000804640000000000000444000000000000024400000000000004440000000000000244000000000000044400000000000003e400000000000003440010300000001000000050000000000000000002e4000000000000014400000000000004440000000000000244000000000000024400000000000003440000000000000144000000000000024400000000000002e400000000000001440',
        ];
        yield 'Point' => [$point, '0101000000c72e51bd353002c01904560e2dbe4a40'];
        yield 'Polygon' => [
            $polygon,
            '0103000000010000000400000000000000000008400000000000000040000000000000104000000000000000400000000000001040000000000000084000000000000008400000000000000040',
        ];
    }

    #[DataProvider('geometryProvider')]
    public function testEncode(GeometryInterface $geometry, string $hex): void
    {
        $this->assertSame($hex, bin2hex(WkbTransformer::encode($geometry)));
    }

    #[DataProvider('geometryProvider')]
    public function testDecode(GeometryInterface $geometry, string $hex): void
    {
        $result = WkbTransformer::decode(hex2bin($hex));
        $this->assertInstanceOf($geometry::class, $result);
        $this->assertSame($geometry->jsonSerialize(), $result->jsonSerialize());
    }
}
