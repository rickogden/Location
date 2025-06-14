<?php

declare(strict_types=1);

/**
 * The radius of the Earth.
 *
 * @author rick
 */

namespace Ricklab\Location\Ellipsoid;

use Override;

final class Earth extends Ellipsoid
{
    protected const RADIUS = 6371009;

    protected const MAJOR_SEMI_AXIS = 6378137;

    protected const MINOR_SEMI_AXIS = '6356752.314245';

    public function __construct()
    {
        parent::__construct(self::RADIUS, self::MAJOR_SEMI_AXIS, self::MINOR_SEMI_AXIS);
    }

    #[Override]
    public function equals(Ellipsoid $ellipsoid): bool
    {
        return $ellipsoid instanceof self || parent::equals($ellipsoid);
    }
}
