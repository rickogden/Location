<?php

declare(strict_types=1);

namespace Ricklab\Location\Decoder;

use Ricklab\Location\Decoder\Traits\CreateGeometryTrait;
use Ricklab\Location\Geometry\GeometryInterface;

final class WktDecoder
{
    use CreateGeometryTrait;

    public static function fromString(string $wkt): GeometryInterface
    {
        $type = \trim(\mb_substr($wkt, 0, \mb_strpos($wkt, '(') ?: 0));
        $wkt = \trim(\str_replace($type, '', $wkt));

        if ('geometrycollection' === \mb_strtolower($type)) {
            $geocol = \preg_replace('/,?\s*([A-Za-z]+\()/', ':$1', $wkt);
            $geocol = \trim($geocol);
            $geocol = \preg_replace('/^\(/', '', $geocol);
            $geocol = \preg_replace('/\)$/', '', $geocol);

            $arrays = [];
            foreach (\explode(':', $geocol) as $subwkt) {
                if ('' !== $subwkt) {
                    $arrays[] = self::fromString($subwkt);
                }
            }
        } else {
            $json = \str_replace([', ', ' ,', '(', ')'], [',', ',', '[', ']'], $wkt);

            if ('point' === \mb_strtolower($type)) {
                $json = \preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '$1, $2', $json);
            } else {
                $json = \preg_replace('/(-?\d+\.?\d*) (-?\d+\.?\d*)/', '[$1, $2]', $json);
            }

            if (null === $json) {
                throw new \InvalidArgumentException('This is not recognised WKT.');
            }
            $arrays = \json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

            if (!$arrays) {
                throw new \InvalidArgumentException('This is not recognised WKT.');
            }

            if ('multipoint' === \mb_strtolower($type)) {
                foreach ($arrays as $index => $points) {
                    if (\is_array($points[0])) {
                        $arrays[$index] = $points[0];
                    }
                }
            }
        }

        return self::createGeometry($type, $arrays);
    }
}
