<?php

declare(strict_types=1);

namespace App\Support;

/**
 * @mixin \BackedEnum
 */
trait OptionableEnum
{
    public static function toOptions(): array
    {
        $out = [];

        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }
}
