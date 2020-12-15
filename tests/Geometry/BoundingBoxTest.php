<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;

class BoundingBoxTest extends TestCase
{
    public function testFromCenter(): void
    {
        $point = new Point(-2, 53);
        $bbox = BoundingBox::fromCenter($point, 2, 'km');

        $this->assertCount(5, $bbox->getPoints());
    }

    public function trueContains(): \Generator
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

    public function falseContains(): \Generator
    {
        $bbox = new BoundingBox(-2, 50, 1, 52);
        yield [$bbox, new Point(0, 54)];
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

    /**
     * @dataProvider falseContains
     */
    public function testContainsFalse(BoundingBox $boundingBox, GeometryInterface $geometry): void
    {
        $this->assertFalse($boundingBox->contains($geometry));
    }
}
