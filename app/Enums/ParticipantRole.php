<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum ParticipantRole: int implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    case Athlete = 0;

    case Manager = 1;

    public function getLabel(): string
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
