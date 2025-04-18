<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum ParticipantRole: int
{
    use ArrayableEnum, OptionableEnum;

    case Athlete = 0;

    case Manager = 1;

    public function label(): string
    {
        return trans('participant.role.'.str($this->name)->slug());
    }

    public function isAthlete(): bool
    {
        return $this === self::Athlete;
    }

    public function isManager(): bool
    {
        return $this === self::Manager;
    }
}
