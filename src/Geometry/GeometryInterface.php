<?php


namespace Ricklab\Location\Geometry;

/**
 * The interface for all the Geomtry objects.
 *
 * Interface GeometryInterface
 * @package Ricklab\Location\Geometry
 */
interface GeometryInterface extends \JsonSerializable
{
    /**
     * Representation of the geometry in Well-Known Text.
     * @return string
     */
    public function toWkt();

    /**
     * The geometry in an embedded array format.
     * @return array
     */
    public function toArray();

    /**
     * Gets all the points in a geometry. Note, order is not necessarily representative.
     * @return Point[]
     */
    public function getPoints();

    /**
     * Returns a string representive of the geometry using spaces to separate lon, lat pairs, commas to separate
     * coordinates, and brackets to separate coordinate groups. E.g. (2 4, 3 5)
     * @return string
     */
    public function __toString();

    /**
     * Returns a GeoJSON representation of the geometry.
     * @return array
     */
    public function jsonSerialize();
}
