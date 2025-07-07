<?php

declare(strict_types=1);

namespace Ricklab\Location\Converter;

final class UnitConverterRegistry
{
    private static ?UnitConverter $unitConverter = null;

    public static function getUnitConverter(): UnitConverter
    {
        return self::$unitConverter ??= new NativeUnitConverter();
    }
}
