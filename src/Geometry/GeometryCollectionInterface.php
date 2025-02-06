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
 *
 * @template T of GeometryInterface
 */
interface GeometryCollectionInterface
{
    /**
     * Returns the geometries in the collection as an array.
     *
     * @return (GeometryInterface&T)[]
     *
     * @psalm-return list<GeometryInterface&T>
     */
    public function getGeometries(): array;
}
