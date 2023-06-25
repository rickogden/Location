<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

use Generator;
use PHPUnit\Framework\TestCase;

class DegreesMinutesSecondsTest extends TestCase
{
    public static function decimalDmsProvider(): Generator
    {
        yield [1.0342916666666668, 'LATITUDE', 1, 2, 3.45, 'N'];
        yield [1.0342916666666668, 'LONGITUDE', 1, 2, 3.45, 'E'];
        yield [-1.0342916666666668, 'LONGITUDE', 1, 2, 3.45, 'W'];
        yield [-1.0342916666666668, 'LATITUDE', 1, 2, 3.45, 'S'];
    }

    /**
     * @dataProvider decimalDmsProvider
     */
    public function testFromDecimal(
        float $dec,
        string $axis,
        int $degrees,
        int $minutes,
        float $seconds,
        string $direction
    ): void {
        $dms = DegreesMinutesSeconds::fromDecimal($dec, $axis);
        $this->assertSame($degrees, $dms->getDegrees());
        $this->assertSame($minutes, $dms->getMinutes());
        $this->assertSame($seconds, round($dms->getSeconds(), 5));
        $this->assertSame($direction, $dms->getDirection());
    }

    /**
     * @dataProvider decimalDmsProvider
     */
    public function testToDecimal(
        float $dec,
        string $axis,
        int $degrees,
        int $minutes,
        float $seconds,
        string $direction
    ): void {
        $dms = new DegreesMinutesSeconds($degrees, $minutes, $seconds, $direction);
        $this->assertSame($axis, $dms->getAxis());
        $this->assertSame($dec, $dms->toDecimal());
    }

    public static function stringDmsProvider(): Generator
    {
        yield ['40° 26′ 46″ N', 40, 26, 46, 'N'];
        yield ['79° 58′ 56″ W', 79, 58, 56, 'W'];
        yield ['40° 26′ 46.2345″ S', 40, 26, 46.2345, 'S'];
        yield ['79° 58′ 56.5543″ E', 79, 58, 56.5543, 'E'];
    }

    public static function malformedStringsToDms(): Generator
    {
        yield ['40 26 46 N', 40, 26, 46, 'N'];
        yield ['79 58 56 W', 79, 58, 56, 'W'];
        yield ['40 26 46.2345 S', 40, 26, 46.2345, 'S'];
        yield ['79 58 56.5543 E', 79, 58, 56.5543, 'E'];
        yield ['-79 58 56.5543 E', -79, 58, 56.5543, 'E'];
    }

    public static function missingElements(): Generator
    {
        yield ['40° 26′ N', 40, 26, 0, 'N'];
        yield ['79° W', 79, 0, 0, 'W'];
        yield ['40° 46.2345" S', 40, 0, 46.2345, 'S'];
        yield ['-79° 58\' E', -79, 58, 0, 'E'];
    }

    /**
     * @dataProvider stringDmsProvider
     */
    public function testToString(string $string, int $degrees, int $minutes, float $seconds, string $direction): void
    {
        $dms = new DegreesMinutesSeconds($degrees, $minutes, $seconds, $direction);
        $this->assertSame($string, $dms->toString());
        $this->assertSame($string, (string) $dms);
    }

    /**
     * @dataProvider stringDmsProvider
     * @dataProvider malformedStringsToDms
     * @dataProvider missingElements
     */
    public function testFromString(string $string, int $degrees, int $minutes, float $seconds, string $direction): void
    {
        $dms = DegreesMinutesSeconds::fromString($string);
        $this->assertSame($degrees, $dms->getDegrees());
        $this->assertSame($minutes, $dms->getMinutes());
        $this->assertSame($seconds, round($dms->getSeconds(), 5));
        $this->assertSame($direction, $dms->getDirection());
    }
}
