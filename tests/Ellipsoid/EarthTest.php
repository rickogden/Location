<?php

declare(strict_types=1);

namespace Ricklab\Location\Ellipsoid;

use PHPUnit\Framework\TestCase;

class EarthTest extends TestCase
{
    public function testFlattening(): void
    {
        $earth = new Earth();
        $this->assertSame(0.003352810664775694, $earth::getFlattening());
    }
}
