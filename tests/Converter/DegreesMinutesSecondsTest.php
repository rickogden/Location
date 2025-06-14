<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DegreesMinutesSeconds::class)]
class DegreesMinutesSecondsTest extends TestCase
{
    public static function decimalDmsProvider(): Generator
    {
        yield [1.0342916666666668, Axis::LATITUDE, 1, 2, 3.45, Direction::NORTH];
        yield [1.0342916666666668, Axis::LONGITUDE, 1, 2, 3.45, Direction::EAST];
        yield [-1.0342916666666668, Axis::LONGITUDE, 1, 2, 3.45, Direction::WEST];
        yield [-1.0342916666666668, Axis::LATITUDE, 1, 2, 3.45, Direction::SOUTH];
    }

    #[DataProvider('decimalDmsProvider')]
    public function testFromDecimal(
        float $dec,
        Axis $axis,
        int $degrees,
        int $minutes,
        float $seconds,
        Direction $direction,
    ): void {
        $dms = DegreesMinutesSeconds::fromDecimal($dec, $axis);
        $this->assertSame($degrees, $dms->getDegrees());
        $this->assertSame($minutes, $dms->getMinutes());
        $this->assertSame($seconds, round($dms->getSeconds(), 5));
        $this->assertSame($direction, $dms->getDirection());
    }

    #[DataProvider('decimalDmsProvider')]
    public function testToDecimal(
        float $dec,
        Axis $axis,
        int $degrees,
        int $minutes,
        float $seconds,
        Direction $direction,
    ): void {
        $dms = new DegreesMinutesSeconds($degrees, $minutes, $seconds, $direction);
        $this->assertSame($axis, $dms->getAxis());
        $this->assertSame($dec, $dms->toDecimal());
    }

    public static function stringDmsProvider(): Generator
    {
        yield ['40° 26′ 46″ N', 40, 26, 46, Direction::NORTH];
        yield ['79° 58′ 56″ W', 79, 58, 56, Direction::WEST];
        yield ['40° 26′ 46.2345″ S', 40, 26, 46.2345, Direction::SOUTH];
        yield ['79° 58′ 56.5543″ E', 79, 58, 56.5543, Direction::EAST];
    }

    public static function malformedStringsToDms(): Generator
    {
        yield ['40 26 46 N', 40, 26, 46, Direction::NORTH];
        yield ['79 58 56 W', 79, 58, 56, Direction::WEST];
        yield ['40 26 46.2345 S', 40, 26, 46.2345, Direction::SOUTH];
        yield ['79 58 56.5543 E', 79, 58, 56.5543, Direction::EAST];
        yield ['-79 58 56.5543 E', -79, 58, 56.5543, Direction::EAST];
    }

    public static function missingElements(): Generator
    {
        yield ['40° 26′ N', 40, 26, 0, Direction::NORTH];
        yield ['79° W', 79, 0, 0, Direction::WEST];
        yield ['40° 46.2345" S', 40, 0, 46.2345, Direction::SOUTH];
        yield ['-79° 58\' E', -79, 58, 0, Direction::EAST];
    }

    #[DataProvider('stringDmsProvider')]
    public function testToString(string $string, int $degrees, int $minutes, float $seconds, Direction $direction): void
    {
        $dms = new DegreesMinutesSeconds($degrees, $minutes, $seconds, $direction);
        $this->assertSame($string, $dms->toString());
        $this->assertSame($string, (string) $dms);
    }

    #[DataProvider('stringDmsProvider')]
    #[DataProvider('malformedStringsToDms')]
    #[DataProvider('missingElements')]
    public function testFromString(string $string, int $degrees, int $minutes, float $seconds, Direction $direction): void
    {
        $dms = DegreesMinutesSeconds::fromString($string);
        $this->assertSame($degrees, $dms->getDegrees());
        $this->assertSame($minutes, $dms->getMinutes());
        $this->assertSame($seconds, round($dms->getSeconds(), 5));
        $this->assertSame($direction, $dms->getDirection());
    }
}
