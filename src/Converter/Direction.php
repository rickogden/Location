<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

/** @psalm-immutable */
enum Direction: string
{
    case NORTH = 'N';
    case SOUTH = 'S';
    case EAST = 'E';
    case WEST = 'W';

    public function invert(): self
    {
        return match ($this) {
            self::NORTH => self::SOUTH,
            self::SOUTH => self::NORTH,
            self::EAST => self::WEST,
            self::WEST => self::EAST,
        };
    }

    public function getAxis(): Axis
    {
        return match ($this) {
            self::NORTH, self::SOUTH => Axis::LATITUDE,
            self::EAST, self::WEST => Axis::LONGITUDE,
        };
    }

    public function multiplier(): int
    {
        return match($this) {
            self::SOUTH, self::WEST => -1,
            self::NORTH, self::EAST => 1,
        };
    }
}
