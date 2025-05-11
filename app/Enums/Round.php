<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum Round: int implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    case Preliminary = 0;

    case Final = 1;

    case SemiFinal = 2;

    case Quarter = 3;

    case RoundOfSixteen = 4;

    case RoundOfThirtytwo = 5;

    public function getLabel(): string
    {
        return trans('app.round.'.str($this->name)->slug());
    }

    public function isFinal(): bool
    {
        return $this === self::Final;
    }
}
