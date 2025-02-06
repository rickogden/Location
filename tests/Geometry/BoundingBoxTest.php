<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('trueContains')]
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

    #[DataProvider('intersectingProvider')]
    #[DataProvider('doesNotIntersectProvider')]
    public function testContainsFalse(BoundingBox $boundingBox, GeometryInterface $geometry): void
    {
        $this->assertFalse($boundingBox->contains($geometry));
    }

    #[DataProvider('trueContains')]
    #[DataProvider('intersectingProvider')]
    public function testIntersects(BoundingBox $boundingBox, GeometryInterface $geometry): void
    {
        $this->assertTrue($boundingBox->intersects($geometry));
    }

    #[DataProvider('doesNotIntersectProvider')]
    public function testNotIntersects(BoundingBox $boundingBox, GeometryInterface $geometry): void
    {
        $this->assertFalse($boundingBox->intersects($geometry));
    }

    public function testGetNorthEast(): void
    {
        $bbox = new BoundingBox(-2, 50, 1, 52);
        $northEast = $bbox->getNorthEast();
        $this->assertInstanceOf(Point::class, $northEast);
        $this->assertSame(1.0, $northEast->getLongitude());
        $this->assertSame(52.0, $northEast->getLatitude());
    }

    public function testGetSouthWest(): void
    {
        $bbox = new BoundingBox(-2, 50, 1, 52);
        $southWest = $bbox->getSouthWest();
        $this->assertInstanceOf(Point::class, $southWest);
        $this->assertSame(-2.0, $southWest->getLongitude());
        $this->assertSame(50.0, $southWest->getLatitude());
    }

    public function testGetCenter(): void
    {
        $bbox = new BoundingBox(-2, 50, 1, 52);
        $center = $bbox->getCenter();
        $this->assertInstanceOf(Point::class, $center);
        $this->assertSame(-0.5, $center->getLongitude());
        $this->assertSame(51.0, $center->getLatitude());
    }

    public function testFromArray(): void
    {
        $array = [-2, 50, 1, 52];
        $bbox = BoundingBox::fromArray($array);
        $this->assertInstanceOf(BoundingBox::class, $bbox);
        $this->assertSame(-2.0, $bbox->getSouthWest()->getLongitude());
        $this->assertSame(50.0, $bbox->getSouthWest()->getLatitude());
        $this->assertSame(1.0, $bbox->getNorthEast()->getLongitude());
        $this->assertSame(52.0, $bbox->getNorthEast()->getLatitude());
    }
}
