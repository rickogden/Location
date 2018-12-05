<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 02/12/2015
 * Time: 11:15.
 */

namespace Ricklab\Location\Geometry;

use PHPUnit\Framework\TestCase;

class MultiPolygonTest extends TestCase
{
    public function testToWkt()
    {
        $wkt = 'MULTIPOLYGON(((1 1, 5 1, 5 5, 1 5, 1 1), (2 2, 3 2, 3 3, 2 3, 2 2)), ((3 3, 6 2, 6 4, 3 3)))';

        $multipolygon = MultiPolygon::fromArray([
            Polygon::fromArray([
                [[1, 1], [5, 1], [5, 5], [1, 5], [1, 1]],
                [[2, 2], [3, 2], [3, 3], [2, 3], [2, 2]],
            ]),
            Polygon::fromArray([[[3, 3], [6, 2], [6, 4], [3, 3]]]),
        ]);

        $this->assertEquals($wkt, $multipolygon->toWkt());
    }
}
