<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use IteratorAggregate;
use Override;
use Ricklab\Location\Geometry\Traits\GeometryTrait;

/**
 * @implements IteratorAggregate<LineString>
 * @implements GeometryCollectionInterface<LineString>
 */
final class MultiLineString implements GeometryInterface, GeometryCollectionInterface, IteratorAggregate
{
    /** @use GeometryTrait<LineString> */
    use GeometryTrait;

    /**
     * @readonly
     *
     * @var list<LineString>
     */
    protected readonly array $geometries;

    #[Override]
    public static function fromArray(array $geometries): self
    {
        $result = [];

        /** @var LineString|array $lineString */
        foreach ($geometries as $lineString) {
            if ($lineString instanceof LineString) {
                $result[] = $lineString;
            } else {
                $result[] = LineString::fromArray($lineString);
            }
        }

        return new self($result);
    }

    /**
     * @param LineString[] $lineStrings
     *
     * @psalm-param list<LineString> $lineStrings
     */
    public function __construct(array $lineStrings)
    {
        $this->geometries = $lineStrings;
    }

    /**
     * @return LineString[] an array of the LineStrings
     *
     * @psalm-return list<LineString> an array of the LineStrings
     */
    #[Override]
    public function getGeometries(): array
    {
        return $this->geometries;
    }

    /**
     * Adds a new LineString to the collection.
     */
    public function withGeometry(LineString $lineString): self
    {
        $geometries = $this->geometries;
        $geometries[] = $lineString;

        return new self($geometries);
    }

    /**
     * Removes a LineString from the collection.
     */
    public function withoutGeometry(LineString $lineString): self
    {
        $geometries = array_filter($this->geometries, fn (LineString $ls): bool => $ls !== $lineString);

        return new self(array_values($geometries));
    }

    /**
     * @return LineString[] an array of the LineStrings
     *
     * @psalm-return list<LineString> an array of the LineStrings
     */
    #[Override]
    public function getChildren(): array
    {
        return $this->geometries;
    }

    #[Override]
    public function getBBox(): BoundingBox
    {
        return BoundingBox::fromGeometry($this);
    }
}
