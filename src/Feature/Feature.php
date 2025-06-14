<?php

declare(strict_types=1);

namespace Ricklab\Location\Feature;

use JsonSerializable;
use Override;
use Ricklab\Location\Geometry\BoundingBox;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Transformer\GeoJsonTransformer;

final class Feature implements JsonSerializable
{
    private readonly string|int|float|null $id;
    private readonly ?GeometryInterface $geometry;

    private readonly array $properties;
    private readonly bool $bbox;
    private ?BoundingBox $bboxCache = null;

    public static function createWithExistingBoundingBox(BoundingBox $bbox, array $properties = [], ?GeometryInterface $geometry = null, float|int|string|null $id = null): self
    {
        $feature = new self($properties, $geometry, $id, true);
        $feature->bboxCache = $bbox;

        return $feature;
    }

    public function __construct(array $properties = [], ?GeometryInterface $geometry = null, float|int|string|null $id = null, bool $bbox = false)
    {
        $this->properties = $properties;
        $this->geometry = $geometry;
        $this->bbox = $bbox;
        $this->id = $id;
    }

    public function withBbox(): self
    {
        return $this->bbox ? $this : new self(
            $this->properties,
            $this->geometry,
            $this->id,
            true
        );
    }

    public function withoutBbox(): self
    {
        return !$this->bbox ? $this : new self(
            $this->properties,
            $this->geometry,
            $this->id,
            false
        );
    }

    public function getGeometry(): ?GeometryInterface
    {
        return $this->geometry;
    }

    public function withGeometry(GeometryInterface $geometry): self
    {
        return new self(
            $this->properties,
            $geometry,
            $this->id,
            $this->bbox
        );
    }

    /**
     * @return array all the properties
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function withProperties(array $properties): self
    {
        $self = new self(
            $properties,
            $this->geometry,
            $this->id,
            $this->bbox
        );

        $self->bboxCache = $this->bboxCache;

        return $self;
    }

    public function getBoundingBox(): ?BoundingBox
    {
        if (!$this->bbox || null === $this->geometry) {
            return null;
        }

        if (null === $this->bboxCache) {
            $this->bboxCache = BoundingBox::fromGeometry($this->geometry);
        }

        return $this->bboxCache;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return GeoJsonTransformer::jsonArray($this);
    }

    /**
     * @return float|int|string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float|int|string|null $id
     */
    public function withId($id): self
    {
        $new = new self($this->properties, $this->geometry, $id, $this->bbox);
        $new->bboxCache = $this->bboxCache;

        return $new;
    }
}
