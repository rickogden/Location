<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

/**
 * The interface for all the Geometry objects.
 *
 * Interface GeometryInterface
 */
interface GeometryInterface extends \JsonSerializable
{
    public static function getWktType(): string;

    public static function getGeoJsonType(): string;

    /**
     * @return self
     */
    public static function fromArray(array $geometries);

    /**
     * Representation of the geometry in Well-Known Text.
     */
    public function toWkt(): string;

    /**
     * The geometry in an embedded array format.
     */
    public function toArray(): array;

    /**
     * Gets all the points in a geometry. Note, order is not necessarily representative.
     *
     * @return Point[]
     */
    public function getPoints(): array;

    /**
     * Returns a string representative of the geometry using spaces to separate lon, lat pairs, commas to separate
     * coordinates, and brackets to separate coordinate groups. E.g. (2 4, 3 5).
     */
    public function __toString(): string;

    /**
     * Returns a GeoJSON representation of the geometry.
     */
    public function jsonSerialize(): array;

    public function equals(GeometryInterface $geometry): bool;
}
