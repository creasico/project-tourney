<?php

declare(strict_types=1);

namespace App\Support;

/**
 * @mixin \BackedEnum
 */
trait ArrayableEnum
{
    public static function toArray(): array
    {
        $key = is_subclass_of(self::class, \BackedEnum::class) ? 'value' : 'name';

        return array_column(self::cases(), $key);
    }
}
