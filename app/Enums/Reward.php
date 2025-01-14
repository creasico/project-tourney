<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum Reward: int
{
    use ArrayableEnum, OptionableEnum;

    case NoMedal = 0;

    case Gold = 1;

    case Silver = 2;

    case Bronse = 3;

    case Certificate = 4;

    public function label(): string
    {
        return trans('tournament.reward.'.str($this->name)->slug());
    }
}
