<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Generator;
use PHPUnit\Framework\TestCase;

class BoundingBoxTest extends TestCase
{
    public function testFromCenter(): void
    {
        $point = new Point(-2, 53);
        $bbox = BoundingBox::fromCenter($point, 2, 'km');

        $this->assertCount(5, $bbox->getPoints());
    }

    public static function trueContains(): Generator
    {
        $bbox = new BoundingBox(-1, 50, 1, 52);
        yield [$bbox, new Point(0, 51)];
        yield [
            $bbox,
            new LineString([
                new Point(-1, 51),
                new Point(1, 51),
                new Point(1, 51.5),
                new Point(0.5, 51.5),
            ]),
        ];
        yield [
            $bbox,
            new Polygon([
                new LineString([
                    new Point(-1, 51),
                    new Point(1, 51),
                    new Point(1, 51.5),
                    new Point(0.5, 51.5),
                ]),
            ]),
        ];
    }

    /**
     * @dataProvider trueContains
     */
    public function testContains(BoundingBox $boundingBox, GeometryInterface $geometry): void
    {
        $this->assertTrue($boundingBox->contains($geometry));
    }

    public static function intersectingProvider(): Generator
    {
        $bbox = new BoundingBox(-2, 50, 1, 52);
        yield [
            $bbox,
            new LineString([
                new Point(-1, 51),
                new Point(1, 51),
                new Point(1, 52.5),
                new Point(0.5, 51.5),
            ]),
        ];
        yield [
            $bbox,
            new Polygon([
                new LineString([
                    new Point(-1, 51),
                    new Point(1, 53),
                    new Point(1, 51.5),
                    new Point(0.5, 51.5),
                ]),
            ]),
        ];
    }

    public static function doesNotIntersectProvider(): Generator
    {
        $bbox = new BoundingBox(-2, 50, 1, 52);
        yield [$bbox, new Point(0, 54)];
        yield [$bbox, new LineString([new Point(0, 53), new Point(0, 55)])];
    }

    /**
     * @dataProvider intersectingProvider
     * @dataProvider doesNotIntersectProvider
     */
    public function testContainsFalse(BoundingBox $boundingBox, GeometryInterface $geometry): void
    {
        $this->assertFalse($boundingBox->contains($geometry));
    }

    /**
     * @dataProvider trueContains
     * @dataProvider intersectingProvider
     */
    public function testIntersects(BoundingBox $boundingBox, GeometryInterface $geometry): void
    {
        $this->assertTrue($boundingBox->intersects($geometry));
    }

    /**
     * @dataProvider doesNotIntersectProvider
     */
    public function testNotIntersects(BoundingBox $boundingBox, GeometryInterface $geometry): void
    {
        $this->assertFalse($boundingBox->intersects($geometry));
    }
}
