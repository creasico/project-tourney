<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum MedalPrize: int implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    case NoMedal = 0;

    case Gold = 1;

    case Silver = 2;

    case Bronse = 3;

    case Certificate = 4;

    public function getLabel(): string
    {
        return trans('tournament.prize.'.str($this->name)->slug());
    }
}
