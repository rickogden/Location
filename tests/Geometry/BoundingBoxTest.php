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
}
