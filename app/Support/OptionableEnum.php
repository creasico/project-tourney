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

    public static function fromLabel(string $label): ?static
    {
        $label = str($label)->lower();

        foreach (self::cases() as $case) {
            if (
                method_exists($case, 'label') &&
                $label->is($case->label(), true)
            ) {
                return $case;
            }

            if ($label->is($case->name, true)) {
                return $case;
            }
        }

        try {
            return self::tryFrom((string) $label);
        } catch (\Throwable $er) {
            return null;
        }
    }
}
