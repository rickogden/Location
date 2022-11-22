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
use Ricklab\Location\Geometry\BoundingBox;
use Ricklab\Location\Geometry\MultiPoint;
use Ricklab\Location\Transformer\GeoJsonTransformer;

final class FeatureCollection implements IteratorAggregate, JsonSerializable
{
    /**
     * @var list<Feature>
     */
    private array $features = [];
    private bool $bbox;
    private ?BoundingBox $bboxCache = null;

    public static function fromGeoJson(array $geojson): self
    {
        $features = array_map(
            static fn (array $feature): Feature => Feature::fromGeoJson($feature),
            $geojson['features'] ?? []
        );

        $collection = new FeatureCollection($features, isset($geojson['bbox']));

        if (isset($geojson['bbox'])) {
            $collection->bboxCache = BoundingBox::fromArray($geojson['bbox']);
        }

        return $collection;
    }

    /**
     * FeatureCollection constructor.
     *
     * @param Feature[] $features
     */
    public function __construct(array $features = [], bool $bbox = false)
    {
        $this->features = (static fn (Feature ...$features): array => $features)(...$features);
        $this->bbox = $bbox;
    }

    public function getBbox(): ?BoundingBox
    {
        if (false === $this->bbox) {
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
        return $this->bbox ? $this : new self(
            $this->features,
            true
        );
    }

    public function withoutBbox(): self
    {
        return !$this->bbox ? $this : new self(
            $this->features,
            false
        );
    }

    public function withFeature(Feature $feature): self
    {
        return new self(
            array_merge($this->features, [$feature]),
            $this->bbox
        );
    }

    public function withoutFeature(Feature $feature): self
    {
        $features = $this->features;
        foreach ($features as $i => $f) {
            if ($f === $feature) {
                unset($this->features[$i]);
                break;
            }
        }

        return new self($features, $this->bbox);
    }

    public function jsonSerialize(): array
    {
        return GeoJsonTransformer::jsonArray($this);
    }

    /**
     * @return ArrayIterator<int, Feature>
     */
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
