<?php
declare(strict_types=1);

namespace Ricklab\Location\Converter;

/** @psalm-immutable */
enum Axis
{
    case LONGITUDE;
    case LATITUDE;

    public function getDirection(): Direction
    {
        return match ($this) {
            self::LONGITUDE => Direction::EAST,
            self::LATITUDE => Direction::NORTH,
        };
    }
}
