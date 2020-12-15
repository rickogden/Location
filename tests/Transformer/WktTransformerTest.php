<?php

declare(strict_types=1);

namespace Ricklab\Location\Transformer;

use PHPUnit\Framework\TestCase;
use Ricklab\Location\Geometry\Point;
use Ricklab\Location\Geometry\MultiPolygon;
use Ricklab\Location\Geometry\MultiLineString;

class WktTransformerTest extends TestCase
{

    public function testEncode(): void
    {
        $multipolywkt = 'MULTIPOLYGON(((1.432 -1.543, 5 1, 5 5, 1 5, 1.432 -1.543), (2 2, 3 2, 3 3, 2 3, 2 2)), ((3 3, 6 2, 6 4, 3 3)))';
        $multilinewkt = 'MULTILINESTRING((3 4, 10 50, 20 25), (-5 -8, -10 -8, -15 -4))';
        $pointwkt = 'POINT(4 5)';
        $multipoly = WktTransformer::decode($multipolywkt);
        $multiline = WktTransformer::decode($multilinewkt);
        $point = WktTransformer::decode($pointwkt);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertInstanceOf(MultiPolygon::class, $multipoly);
        $this->assertInstanceOf(MultiLineString::class, $multiline);
        $this->assertEquals([4, 5], $point->toArray());
        $this->assertEquals($multipolywkt, WktTransformer::encode($multipoly));
        $this->assertEquals($multilinewkt, WktTransformer::encode($multiline));
        $this->assertEquals($pointwkt, WktTransformer::encode($point));
    }
}
