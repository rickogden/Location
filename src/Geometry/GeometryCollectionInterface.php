<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 05/01/2016
 * Time: 14:46.
 */

namespace Ricklab\Location\Geometry;

/**
 * The interface for all collection geometry types.
 *
 * Interface GeometryCollectionInterface
 */
interface GeometryCollectionInterface
{
    /**
     * Returns the geometries in the collection as an array.
     *
     * @return GeometryInterface[]
     *
     * @psalm-return list<GeometryInterface>
     */
    public function getGeometries(): array;
}
