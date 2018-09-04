<?php
/**
 * Author: rick
 * Date: 05/01/2016
 * Time: 14:46
 */

namespace Ricklab\Location\Geometry;

/**
 * The interface for all collection geometry types.
 *
 * Interface GeometryCollectionInterface
 * @package Ricklab\Location\Geometry
 */
interface GeometryCollectionInterface
{

    /**
     * Returns the geometries in the collection as an array
     * @return GeometryInterface[]
     */
    public function getGeometries();
}
