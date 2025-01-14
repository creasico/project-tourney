<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum ParticipantType: int
{
    use ArrayableEnum, OptionableEnum;

    case Contestant = 0;

    case PIC = 1;

    public function label(): string
    {
        return trans('tournament.participant_type.'.str($this->name)->slug());
    }
}
