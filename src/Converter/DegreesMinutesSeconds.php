<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

use function count;

use InvalidArgumentException;

use function sprintf;

/**
 * @psalm-immutable
 */
final class DegreesMinutesSeconds
{
    private const REGEX = '/(-?\d+)\D+(\d+)\D+(\d+.?\d+)\D+([NWSE])/';

    public static function fromString(string $string): self
    {
        $string = mb_trim($string);

        $degreesMatch = [];
        $minuteMatch = [];
        $secondMatch = [];
        $directionMatch = [];

        if (
            preg_match('/(-?\d+)°/u', $string, $degreesMatch)
            && preg_match('/([NSEW])/', $string, $directionMatch)
        ) {
            preg_match('/(\d+)[\'′]/u', $string, $minuteMatch);
            preg_match('/(\d+.?\d+)["″]/u', $string, $secondMatch);
            $degrees = (int) $degreesMatch[1];
            $minutes = (int) ($minuteMatch[1] ?? 0);
            $seconds = (float) ($secondMatch[1] ?? 0);
            $direction = Direction::from((string) end($directionMatch));

            return new self($degrees, $minutes, $seconds, $direction);
        }

        $results = [];
        $success = preg_match(self::REGEX, $string, $results);

        if ($success && 5 === count($results)) {
            /**
             * @var array{
             *     0: string,
             *     1: numeric-string,
             *     2: numeric-string,
             *     3: numeric-string,
             *     4: string,
             * } $results
             */
            return new self(
                (int) $results[1],
                (int) $results[2],
                $results[3],
                Direction::from($results[4]),
            );
        }

        throw new InvalidArgumentException('Unable to determine Degrees minutes seconds from string.');
    }

    /**
     * @param float|numeric-string $decimal
     */
    public static function fromDecimal(float|string $decimal, Axis $axis): self
    {
        $decimal = (float) $decimal;

        $direction = $axis->getDirection();

        if (0 > $decimal) {
            $direction = $direction->invert();
            $decimal *= -1.0;
        }

        $deg = (int) floor($decimal);
        $min = (int) floor(($decimal - (float) $deg) * 60.0);
        $sec = ($decimal - (float) $deg - (float) $min / 60.0) * 3600.0;

        return new self($deg, $min, $sec, $direction);
    }

    /**
     * @param float|numeric-string $seconds
     */
    public function __construct(
        private readonly int $degrees,
        private readonly int $minutes,
        private readonly float|string $seconds,
        private readonly Direction $direction,
    ) {
    }

    public function getDegrees(): int
    {
        return $this->degrees;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getSeconds(): float
    {
        return (float) $this->seconds;
    }

    public function getSecondsString(): string
    {
        return (string) $this->seconds;
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }

    public function getAxis(): Axis
    {
        return $this->direction->getAxis();
    }

    public function toDecimal(): float
    {
        $decimal = (float) $this->degrees + ((float) $this->minutes / 60.0) + ((float) $this->seconds / 3600.0);

        return $decimal * (float) $this->direction->multiplier();
    }

    /**
     * @return array{0: int, 1: int, 2: float, 3: Direction} of degrees, minutes, seconds and direction
     */
    public function toArray(): array
    {
        return [
            $this->degrees,
            $this->minutes,
            (float) $this->seconds,
            $this->direction,
        ];
    }

    public function toString(): string
    {
        return sprintf(
            '%d° %d′ %s″ %s',
            $this->degrees,
            $this->minutes,
            $this->seconds,
            $this->direction->value,
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
