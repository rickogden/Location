<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 05/11/2015
 * Time: 15:26.
 */

namespace Ricklab\Location\Feature;

abstract class FeatureAbstract implements \JsonSerializable
{
    protected bool $bbox = false;

    /**
     * @param bool|true $bbox
     */
    public function withBbox(bool $bbox = true): void
    {
        $this->bbox = (bool) $bbox;
    }
}
