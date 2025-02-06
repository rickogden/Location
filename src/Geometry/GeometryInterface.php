<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

use JsonSerializable;

/**
 * The interface for all the Geometry objects.
 *
 * Interface GeometryInterface
 */
interface GeometryInterface extends JsonSerializable, \Stringable
{
    public static function fromArray(array $geometries): self;

    /**
     * The geometry in an embedded array format.
     */
    public function toArray(): array;

    /**
     * Gets all the points in a geometry. Note, order is not necessarily representative.
     *
     * @return Point[]
     *
     * @psalm-return list<Point>
     */
    public function getPoints(): array;

    /**
     * Returns a string representative of the geometry using spaces to separate lon, lat pairs, commas to separate
     * coordinates, and brackets to separate coordinate groups. E.g. (2 4, 3 5).
     */
    public function __toString(): string;

    /**
     * @return string A representation of the geometry for use in generating WKT
     */
    public function wktFormat(): string;

    /**
     * Returns a GeoJSON representation of the geometry.
     */
    public function jsonSerialize(): array;

    /**
     * @psalm-assert-if-true self $geometry
     */
    public function equals(GeometryInterface $geometry): bool;

    public function getBBox(): BoundingBox;
}
