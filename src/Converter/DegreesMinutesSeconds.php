<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

final class DegreesMinutesSeconds
{
    public const DIRECTION_N = 'N';
    public const DIRECTION_S = 'S';
    public const DIRECTION_E = 'E';
    public const DIRECTION_W = 'W';

    public const AXIS_LONGITUDE = 'LONGITUDE';
    public const AXIS_LATITUDE = 'LATITUDE';

    private const DIRECTIONS = [
        self::DIRECTION_N,
        self::DIRECTION_S,
        self::DIRECTION_E,
        self::DIRECTION_W,
    ];

    private const AXES = [
        self::AXIS_LONGITUDE,
        self::AXIS_LATITUDE,
    ];

    private const AXIS_TO_DIRECTION = [
        self::AXIS_LONGITUDE => self::DIRECTION_E,
        self::AXIS_LATITUDE => self::DIRECTION_N,
    ];

    private const DIRECTION_TO_AXIS = [
        self::DIRECTION_W => self::AXIS_LONGITUDE,
        self::DIRECTION_E => self::AXIS_LONGITUDE,
        self::DIRECTION_N => self::AXIS_LATITUDE,
        self::DIRECTION_S => self::AXIS_LATITUDE,
    ];

    private const INVERT_DIRECTION = [
        self::DIRECTION_N => self::DIRECTION_S,
        self::DIRECTION_S => self::DIRECTION_N,
        self::DIRECTION_W => self::DIRECTION_E,
        self::DIRECTION_E => self::DIRECTION_W,
    ];

    private const REGEX = '/(-?\d+)[^\d]+(\d+)[^\d]+(\d+.?\d+)[^\d]+([NWSE])/';

    private int $degrees;
    private int $minutes;
    private float $seconds;

    /** @var self::DIRECTION_N|self::DIRECTION_S|self::DIRECTION_E|self::DIRECTION_W */
    private string $direction;

    public static function fromString(string $string): self
    {
        $string = \trim($string);

        $degreesMatch = [];
        $minuteMatch = [];
        $secondMatch = [];
        $directionMatch = [];

        if (
            \preg_match('/(-?\d+)°/u', $string, $degreesMatch)
            && \preg_match('/([NSEW])/', $string, $directionMatch)
        ) {
            \preg_match('/(\d+)[\'′]/u', $string, $minuteMatch);
            \preg_match('/(\d+.?\d+)["″]/u', $string, $secondMatch);
            $degrees = (int) $degreesMatch[1];
            $minutes = (int) ($minuteMatch[1] ?? 0);
            $seconds = (float) ($secondMatch[1] ?? 0);
            $direction = (string) \end($directionMatch);

            return new self($degrees, $minutes, $seconds, $direction);
        }

        $results = [];
        $success = \preg_match(self::REGEX, $string, $results);

        if ($success && 5 === \count($results)) {
            return new self((int) $results[1], (int) $results[2], (float) $results[3], (string) $results[4]);
        }

        throw new \InvalidArgumentException('Unable to determine Degrees minutes seconds from string.');
    }

    /**
     * @param self::AXIS_LONGITUDE | self::AXIS_LATITUDE $axis
     */
    public static function fromDecimal(float $decimal, string $axis): self
    {
        if (!\in_array($axis, self::AXES)) {
            throw new \InvalidArgumentException('Axis must either be "LONGITUDE" or "LATITUDE"');
        }

        $direction = self::AXIS_TO_DIRECTION[$axis];

        if (0 > $decimal) {
            $direction = self::INVERT_DIRECTION[$direction];
            $decimal *= -1;
        }

        $deg = (int) \floor($decimal);
        $min = (int) \floor(($decimal - $deg) * 60);
        $sec = ($decimal - $deg - $min / 60) * 3600;

        return new self($deg, $min, $sec, $direction);
    }

    public function __construct(int $degrees, int $minutes, float $seconds, string $direction)
    {
        if (!\in_array($direction, self::DIRECTIONS, true)) {
            throw new \InvalidArgumentException(\sprintf('Direction must be one of: "N", "S", "E", "W", %s passed', $direction));
        }

        $this->degrees = $degrees;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
        $this->direction = $direction;
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
        return $this->seconds;
    }

    /**
     * @return self::DIRECTION_N | self::DIRECTION_S | self::DIRECTION_E | self::DIRECTION_W
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @return self::AXIS_LONGITUDE | self::AXIS_LATITUDE
     */
    public function getAxis(): string
    {
        return self::DIRECTION_TO_AXIS[$this->direction];
    }

    public function toDecimal(): float
    {
        $decimal = $this->degrees + ($this->minutes / 60) + ($this->seconds / 3600);

        if (self::DIRECTION_S === $this->direction || self::DIRECTION_W === $this->direction) {
            $decimal *= -1;
        }

        return $decimal;
    }

    /**
     * @return array{0: int, 1: int, 2: float, 3: self::DIRECTION_N | self::DIRECTION_S | self::DIRECTION_E | self::DIRECTION_W} of degrees, minutes, seconds and direction
     */
    public function toArray(): array
    {
        return [
            $this->degrees,
            $this->minutes,
            $this->seconds,
            $this->direction,
        ];
    }

    public function toString(): string
    {
        return \sprintf(
            '%d° %d′ %s″ %s',
            $this->degrees,
            $this->minutes,
            $this->seconds,
            $this->direction
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
