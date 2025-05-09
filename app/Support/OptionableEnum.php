<?php

declare(strict_types=1);

namespace App\Support;

use Filament\Support\Contracts\HasLabel;

/**
 * @mixin \BackedEnum
 */
trait OptionableEnum
{
    public static function toOptions(): array
    {
        $out = [];

        foreach (self::cases() as $case) {
            $out[$case->value] = $case instanceof HasLabel ? $case->getLabel() : $case->name;
        }

        return $out;
    }

    public static function fromLabel(string $label): ?static
    {
        $label = str($label)->lower();

        foreach (self::cases() as $case) {
            if ($case instanceof HasLabel && $label->is($case->getLabel(), true)) {
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
