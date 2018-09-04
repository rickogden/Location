<?php

declare(strict_types=1);

namespace Ricklab\Location\Geometry;

/**
 * The interface for all the Geomtry objects.
 *
 * Interface GeometryInterface
 */
interface GeometryInterface extends \JsonSerializable
{
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
     * Returns a string representive of the geometry using spaces to separate lon, lat pairs, commas to separate
     * coordinates, and brackets to separate coordinate groups. E.g. (2 4, 3 5).
     */
    public function __toString(): string;

    /**
     * Returns a GeoJSON representation of the geometry.
     */
    public function jsonSerialize(): array;
}
