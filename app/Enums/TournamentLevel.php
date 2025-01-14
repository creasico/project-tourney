<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum TournamentLevel: int
{
    use ArrayableEnum, OptionableEnum;

    case Nation = 1;

    case Province = 2;

    case Regency = 3;

    case District = 4;

    public function label(): string
    {
        return trans('tournament.level.'.str($this->name)->slug());
    }
}
