<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

class GeoHash
{
    private const HASH_MAP = [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'j',
        'k',
        'm',
        'n',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];

    private string $hash;

    public static function fromPoint(Point $point, int $resolution = 12): self
    {
        if (12 < $resolution || 1 > $resolution) {
            throw new \InvalidArgumentException('Resolution must be between 1 and 12.');
        }

        $longitude = $point->getLongitude();
        $latitude = $point->getLatitude();
        $idx = 0;
        $bit = 0;
        $minLon = Point::MIN_LONGITUDE;
        $maxLon = Point::MAX_LONGITUDE;
        $minLat = Point::MIN_LATITUDE;
        $maxLat = Point::MAX_LATITUDE;
        $i = 0;

        $hash = [];

        while (\count($hash) < $resolution) {
            if (0 === $i % 2) {
                $midLon = ($minLon + $maxLon) / 2;

                if ($longitude >= $midLon) {
                    $idx = $idx * 2 + 1;
                    $minLon = $midLon;
                } else {
                    $idx *= 2;
                    $maxLon = $midLon;
                }
            } else {
                $midLat = ($minLat + $maxLat) / 2;

                if ($latitude >= $midLat) {
                    $idx = $idx * 2 + 1;
                    $minLat = $midLat;
                } else {
                    $idx *= 2;
                    $maxLat = $midLat;
                }
            }

            if (5 === ++$bit) {
                $hash[] = self::HASH_MAP[$idx];
                $bit = 0;
                $idx = 0;
            }

            ++$i;
        }

        return new self(\implode($hash));
    }

    public static function fromString(string $hash): self
    {
        $hash = \mb_strtolower($hash);
        $h = \mb_str_split($hash);
        foreach ($h as $i => $char) {
            if (!\in_array($char, self::HASH_MAP, true)) {
                throw new \InvalidArgumentException(\sprintf('Invalid character "%s" at position %d', $char, $i));
            }
        }

        return new self($hash);
    }

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public function __toString(): string
    {
        return $this->hash;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getBounds(): BoundingBox
    {
        $minLon = Point::MIN_LONGITUDE;
        $maxLon = Point::MAX_LONGITUDE;
        $minLat = Point::MIN_LATITUDE;
        $maxLat = Point::MAX_LATITUDE;
        $i = 0;

        $hash = \mb_str_split($this->hash);
        $indexes = \array_flip(self::HASH_MAP);
        $evenBit = true;

        foreach ($hash as $char) {
            $index = $indexes[$char];

            for ($i = 4; $i >= 0; --$i) {
                $bitN = $index >> $i & 1;

                if ($evenBit) {
                    $midLon = ($minLon + $maxLon) / 2;

                    if (1 === $bitN) {
                        $minLon = $midLon;
                    } else {
                        $maxLon = $midLon;
                    }
                } else {
                    $midLat = ($minLat + $maxLat) / 2;

                    if (1 === $bitN) {
                        $minLat = $midLat;
                    } else {
                        $maxLat = $midLat;
                    }
                }

                $evenBit = !$evenBit;
            }
        }

        return new BoundingBox($minLon, $minLat, $maxLon, $maxLat);
    }

    public function getCenter(): Point
    {
        return $this->getBounds()->getCenter();
    }
}
