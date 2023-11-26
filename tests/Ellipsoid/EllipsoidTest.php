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
}
