<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use PHPUnit\Framework\TestCase;

class EllipsoidTest extends TestCase
{
    public function testFromRadius(): void
    {
        $e = Ellipsoid::fromRadius(2000);
        $this->assertSame(2000.0, $e->radius());
        $this->assertSame(2000.0, $e->majorSemiAxis());
        $this->assertSame(2000.0, $e->minorSemiAxis());
        $this->assertSame(0, $e->flattening());
    }

    public function testFromSemiAxes(): void
    {
        $e = Ellipsoid::fromSemiAxes(4000, 2000);
        $this->assertSame(3000.0, $e->radius());
        $this->assertSame(4000.0, $e->majorSemiAxis());
        $this->assertSame(2000.0, $e->minorSemiAxis());
        $this->assertSame(0.5, $e->flattening());
    }

    public function testEllipsoidEqualsIdentical(): void
    {
        $e1 = Ellipsoid::fromRadius(2000);
        $this->assertTrue($e1->equals($e1));
    }

    public function testEllipsoidEqualsEarth(): void
    {
        $e1 = new Earth();
        $e2 = new Ellipsoid($e1->radius(), $e1->majorSemiAxis(), $e1->minorSemiAxis());
        $this->assertTrue($e1->equals($e2));
        $this->assertTrue($e2->equals($e1));
    }

    public function testEllipsoidNotEquals(): void
    {
        $e1 = Ellipsoid::fromRadius(2000);
        $e2 = Ellipsoid::fromRadius(2001);
        $this->assertFalse($e1->equals($e2));
        $this->assertFalse($e2->equals($e1));
    }

    public function marsNotEqualsEarth(): void
    {
        $e1 = new Earth();
        $e2 = new Mars();
        $this->assertFalse($e1->equals($e2));
        $this->assertFalse($e2->equals($e1));
    }
}
