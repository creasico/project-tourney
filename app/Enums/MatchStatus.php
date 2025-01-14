<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum MatchStatus: int
{
    use ArrayableEnum, OptionableEnum;

    case Queue = 0;

    case Win = 1;

    case Lose = 2;

    public function label(): string
    {
        return trans('match.status.'.str($this->name)->slug());
    }
}
