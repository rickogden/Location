<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;

class GeoHashTest extends TestCase
{
    public function geoHahProvider(): \Generator
    {
        yield ['u4pruydqqvj', 10.40744, 57.64911];
    }

    /**
     * @dataProvider geoHahProvider
     */
    public function testGeoHashFromPoint(string $hash, float $lon, float $lat): void
    {
        $point = new Point($lon, $lat);
        $geoHash = GeoHash::fromPoint($point);
        $this->assertSame($hash, $geoHash->getHash());
    }
}
