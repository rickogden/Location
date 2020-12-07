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

    private const NEIGHBOUR = [
        'n' => ['p0r21436x8zb9dcf5h7kjnmqesgutwvy', 'bc01fg45238967deuvhjyznpkmstqrwx'],
        's' => ['14365h7k9dcfesgujnmqp0r2twvyx8zb', '238967debc01fg45kmstqrwxuvhjyznp'],
        'e' => ['bc01fg45238967deuvhjyznpkmstqrwx', 'p0r21436x8zb9dcf5h7kjnmqesgutwvy'],
        'w' => ['238967debc01fg45kmstqrwxuvhjyznp', '14365h7k9dcfesgujnmqp0r2twvyx8zb'],
    ];

    private const BORDER = [
        'n' => ['prxz', 'bcfguvyz'],
        's' => ['028b', '0145hjnp'],
        'e' => ['bcfguvyz', 'prxz'],
        'w' => ['0145hjnp', '028b'],
    ];

    private string $hash;

    private ?BoundingBox $bounds = null;

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

    public function __construct(string $hash)
    {
        $hash = \mb_strtolower($hash);
        $h = \mb_str_split($hash);
        foreach ($h as $i => $char) {
            if (!\in_array($char, self::HASH_MAP, true)) {
                throw new \InvalidArgumentException(\sprintf('Invalid character "%s" at position %d', $char, $i));
            }
        }

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

    public function getLength(): int
    {
        return \mb_strlen($this->hash);
    }

    public function getBounds(): BoundingBox
    {
        if (null !== $this->bounds) {
            return $this->bounds;
        }

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

        $this->bounds = new BoundingBox($minLon, $minLat, $maxLon, $maxLat);

        return $this->bounds;
    }

    public function getCenter(): Point
    {
        return $this->getBounds()->getCenter();
    }

    public function getAdjacentNorth(): GeoHash
    {
        return self::getAdjacent($this->hash, 'n');
    }

    public function getAdjacentSouth(): GeoHash
    {
        return self::getAdjacent($this->hash, 's');
    }

    public function getAdjacentEast(): GeoHash
    {
        return self::getAdjacent($this->hash, 'e');
    }

    public function getAdjacentWest(): GeoHash
    {
        return self::getAdjacent($this->hash, 'w');
    }

    public function getAdjacentNorthWest(): GeoHash
    {
        return $this->getAdjacentNorth()->getAdjacentWest();
    }

    public function getAdjacentNorthEast(): GeoHash
    {
        return $this->getAdjacentNorth()->getAdjacentEast();
    }

    public function getAdjacentSouthWest(): GeoHash
    {
        return $this->getAdjacentSouth()->getAdjacentWest();
    }

    public function getAdjacentSouthEast(): GeoHash
    {
        return $this->getAdjacentSouth()->getAdjacentEast();
    }

    private static function getAdjacent(string $hash, string $direction): GeoHash
    {
        $lastChar = \mb_substr($hash, -1);
        $parent = \mb_substr($hash, 0, -1);
        $type = \mb_strlen($hash) % 2;

        if (false !== \mb_strpos(self::BORDER[$direction][$type], $lastChar) && '' !== $parent) {
            $parent = self::getAdjacent($parent, $direction);
        }

        return new self($parent.self::HASH_MAP[\mb_strpos(self::NEIGHBOUR[$direction][$type], $lastChar)]);
    }

    public function getParent(): GeoHash
    {
        if ($this->getLength() < 2) {
            throw new \LogicException('This GeoHash has no parent');
        }

        return new self(\mb_substr($this->hash, 0, -1));
    }

    public function equals(self $geoHash): bool
    {
        return $this->hash === $geoHash->hash;
    }

    public function contains(self $geoHash): bool
    {
        if ($this->getLength() >= $geoHash->getLength()) {
            return $this->equals($geoHash);
        }

        $childSubstr = \mb_substr($geoHash->hash, 0, $this->getLength());

        return $this->hash === $childSubstr;
    }
}
