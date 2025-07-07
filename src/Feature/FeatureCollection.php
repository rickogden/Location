<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 16:47.
 */

namespace Ricklab\Location\Feature;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Override;
use Ricklab\Location\Geometry\BoundingBox;
use Ricklab\Location\Geometry\MultiPoint;
use Ricklab\Location\Transformer\GeoJsonTransformer;

/**
 * @implements IteratorAggregate<Feature>
 */
final class FeatureCollection implements IteratorAggregate, JsonSerializable
{
    /**
     * @var list<Feature>
     */
    private readonly array $features;
    private readonly bool $withBbox;
    private ?BoundingBox $bboxCache = null;

    /**
     * @param Feature[] $features
     *
     * @psalm-param list<Feature> $features
     */
    public static function createWithExistingBoundingBox(BoundingBox $bbox, array $features = []): self
    {
        $fc = new self($features, true);
        $fc->bboxCache = $bbox;

        return $fc;
    }

    /**
     * FeatureCollection constructor.
     *
     * @param Feature[] $features
     *
     * @psalm-param list<Feature> $features
     */
    public function __construct(array $features = [], bool $bbox = false)
    {
        $this->features = $features;
        $this->withBbox = $bbox;
    }

    public function getBbox(): ?BoundingBox
    {
        if (false === $this->withBbox) {
            return null;
        }

        if (null === $this->bboxCache) {
            $points = [];
            foreach ($this->features as $feature) {
                $geometry = $feature->getGeometry();

                if (null !== $geometry) {
                    array_push($points, ...$geometry->getPoints());
                }
            }

            $this->bboxCache = BoundingBox::fromGeometry(new MultiPoint($points));
        }

        return $this->bboxCache;
    }

    public function withBbox(): self
    {
        return $this->withBbox ? $this : new self(
            $this->features,
            true
        );
    }

    public function withoutBbox(): self
    {
        return !$this->withBbox ? $this : new self(
            $this->features,
            false
        );
    }

    public function withFeature(Feature $feature): self
    {
        return new self(
            array_merge($this->features, [$feature]),
            $this->withBbox
        );
    }

    public function withoutFeature(Feature $feature): self
    {
        $features = $this->features;
        foreach ($features as $i => $f) {
            if ($f === $feature) {
                unset($features[$i]);
                break;
            }
        }

        return new self(array_values($features), $this->withBbox);
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return GeoJsonTransformer::jsonArray($this);
    }

    /**
     * @return ArrayIterator<int<0, max>, Feature>
     */
    #[Override]
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->features);
    }

    /**
     * @return Feature[]
     *
     * @psalm-return list<Feature>
     */
    public function getFeatures(): array
    {
        return $this->features;
    }
}
