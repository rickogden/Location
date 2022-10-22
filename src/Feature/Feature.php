<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 11:14.
 */

namespace Ricklab\Location\Feature;

use InvalidArgumentException;

use function is_string;

use JsonSerializable;
use Ricklab\Location\Geometry\BoundingBox;
use Ricklab\Location\Geometry\GeometryInterface;
use Ricklab\Location\Transformer\GeoJsonTransformer;

class Feature implements JsonSerializable
{
    /**
     * @var string|int|float|null
     */
    protected $id;

    protected ?GeometryInterface $geometry;
    protected array $properties = [];
    private bool $bbox;
    private ?BoundingBox $bboxCache = null;

    public static function fromGeoJson(array $geojson): self
    {
        if (isset($geojson['geometry'])) {
            $decodedGeo = GeoJsonTransformer::fromArray($geojson['geometry']);

            if (!$decodedGeo instanceof GeometryInterface) {
                throw new InvalidArgumentException('Cannot parse geometry in feature');
            }
        }

        $feature = new self($geojson['properties'] ?? [], $decodedGeo ?? null, $geojson['id'] ?? null, isset($geojson['bbox']));

        if (isset($geojson['bbox'])) {
            $feature->bboxCache = BoundingBox::fromArray($geojson['bbox']);
        }

        return $feature;
    }

    /**
     * @param string|int|float|null $id
     */
    public function __construct(array $properties = [], ?GeometryInterface $geometry = null, $id = null, bool $bbox = false)
    {
        if (null !== $id && !is_string($id) && !is_numeric($id)) {
            throw new InvalidArgumentException('$id must be either a string or number.');
        }

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

    public function jsonSerialize(): array
    {
        $array = [];

        if (null !== $this->id) {
            $array['id'] = $this->id;
        }

        $array['type'] = 'Feature';

        if ($this->geometry instanceof GeometryInterface) {
            $bbox = $this->getBoundingBox();

            if (null !== $bbox) {
                $array['bbox'] = $bbox->getBounds();
            }
            $array['geometry'] = $this->geometry->jsonSerialize();
        } else {
            $array['geometry'] = null;
        }

        $array['properties'] = $this->properties;

        return $array;
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
