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
        $geoHash = new GeoHash($hash);
        $centrePoint = $geoHash->getCenter()->round($precision);
        $expected = new Point($lon, $lat);
        $this->assertTrue(
            $expected->equals($centrePoint->round($precision)),
            \sprintf('Expected: %s, Actual: %s', (string) $expected, (string) $centrePoint)
        );
    }

    public function geoHashNeighborProvider(): \Generator
    {
        yield 'Arbitrary 5 character geohash' => [
            'gbsuv',
            'gbsvh',
            'gbsvj',
            'gbsvn',
            'gbsuu',
            'gbsuy',
            'gbsus',
            'gbsut',
            'gbsuw',
        ];

        yield 'Arbitrary 7 character geohash' => [
            'gbsuv7z',
            'gbsuvkn',
            'gbsuvkp',
            'gbsuvs0',
            'gbsuv7y',
            'gbsuveb',
            'gbsuv7w',
            'gbsuv7x',
            'gbsuve8',
        ];

        yield 'The best pub in the world 8 character geohash' => [
            'gcqryemv',
            'gcqryemw',
            'gcqryemy',
            'gcqryeqn',
            'gcqryemt',
            'gcqryeqj',
            'gcqryems',
            'gcqryemu',
            'gcqryeqh',
        ];

        yield 'Border geohash 5 characters' => [
            'g0000',
            'fbpbr',
            'g0002',
            'g0003',
            'fbpbp',
            'g0001',
            'dzzzz',
            'epbpb',
            'epbpc',
        ];

        yield 'Border geohash 9 characters' => [
            'fbpbpbpbp',
            'fbpbpbpbq',
            'fbpbpbpbr',
            'g00000002',
            'fbpbpbpbn',
            'g00000000',
            'dzzzzzzzy',
            'dzzzzzzzz',
            'epbpbpbpb',
        ];
    }

    /**
     * @dataProvider geoHashNeighborProvider
     */
    public function testAdjacent(
        string $origin,
        string $northWest,
        string $north,
        string $northEast,
        string $west,
        string $east,
        string $southWest,
        string $south,
        string $southEast
    ): void {
        $geoHash = new GeoHash($origin);
        $this->assertSame($north, (string) $geoHash->getAdjacentNorth());
        $this->assertSame($south, (string) $geoHash->getAdjacentSouth());
        $this->assertSame($east, (string) $geoHash->getAdjacentEast());
        $this->assertSame($west, (string) $geoHash->getAdjacentWest());
        $this->assertSame($northWest, (string) $geoHash->getAdjacentNorthWest());
        $this->assertSame($northEast, (string) $geoHash->getAdjacentNorthEast());
        $this->assertSame($southWest, (string) $geoHash->getAdjacentSouthWest());
        $this->assertSame($southEast, (string) $geoHash->getAdjacentSouthEast());
    }

    public function testEqualsTrue(): void
    {
        $geoHash = new GeoHash('gcqryemv');
        $geoHash2 = new GeoHash('gcqryemv');
        $this->assertTrue($geoHash->equals($geoHash2), 'True is not returned for equals.');
        $this->assertTrue($geoHash2->equals($geoHash), 'True is not returned for equals.');
    }

    public function notEqualsProvider(): \Generator
    {
        yield 'Not equals' => [new GeoHash('gcqryemv'), new GeoHash('gcqryemy')];
        yield 'Contains' => [new GeoHash('gcqryemv'), new GeoHash('gcqryem')];
    }

    /**
     * @dataProvider notEqualsProvider
     */
    public function testEqualsFalse(GeoHash $geoHash, GeoHash $geoHash2): void
    {
        $this->assertFalse($geoHash->equals($geoHash2), 'False is not returned for equals.');
        $this->assertFalse($geoHash2->equals($geoHash), 'False is not returned for equals.');
    }

    public function containsProvider(): \Generator
    {
        yield 'Direct parent' => [new GeoHash('gcqryem'), new GeoHash('gcqryemy')];
        yield 'Very high parent' => [new GeoHash('gcq'), new GeoHash('gcqryemy')];
    }

    /**
     * @dataProvider containsProvider
     */
    public function testContainsTrue(GeoHash $parent, GeoHash $child): void
    {
        $this->assertTrue($parent->contains($child), 'Parent does not contain the child');
        $this->assertFalse($child->contains($parent), 'Parent contains the child');
    }

    public function testContainsTrueWhenEqual(): void
    {
        $parent = new GeoHash('gcqryemy');
        $child = new GeoHash('gcqryemy');

        $this->assertTrue($parent->contains($child));
        $this->assertTrue($child->contains($parent));
    }

    public function testContainsFalse(): void
    {
        $parent = new GeoHash('gcqryemy');
        $child = new GeoHash('gcqryemv');

        $this->assertFalse($parent->contains($child), 'Parent contains child.');
        $this->assertFalse($child->contains($parent), 'Child contains parent.');
    }
}
