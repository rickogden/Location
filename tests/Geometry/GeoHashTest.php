<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ricklab\Location\Geometry\GeoHash
 */
class GeoHashTest extends TestCase
{
    public function geoHahProvider(): \Generator
    {
        yield ['u4pruydqqvj', 10.40744, 57.64911, 5];
        yield ['gbsuv7s0k', -4.333913, 48.666751, 6];
        yield ['gbsuv7s0m', -4.33387, 48.666751, 6];
        yield ['gbsu', -4.39, 48.6, 2];
    }

    /**
     * @dataProvider geoHahProvider
     * @covers ::fromPoint
     */
    public function testGeoHashFromPoint(string $hash, float $lon, float $lat, float $precision): void
    {
        $point = new Point($lon, $lat);
        $geoHash = GeoHash::fromPoint($point, \mb_strlen($hash));
        $this->assertSame($hash, $geoHash->getHash());
    }

    /**
     * @dataProvider geoHahProvider
     * @covers ::getBounds
     * @covers ::getCenter
     */
    public function testGetCenter(string $hash, float $lon, float $lat, int $precision): void
    {
        $geoHash = GeoHash::fromString($hash);
        $centrePoint = $geoHash->getCenter()->round($precision);
        $expected = new Point($lon, $lat);
        $this->assertTrue(
            $expected->equals($centrePoint->round($precision)),
            \sprintf('Expected: %s, Actual: %s', (string) $expected, (string) $centrePoint)
        );
    }
}
