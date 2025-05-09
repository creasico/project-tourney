<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum TournamentLevel: int implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    case Nation = 1;

    case Province = 2;

    case Regency = 3;

    case District = 4;

    public function getLabel(): string
    {
        return trans('tournament.level.'.str($this->name)->slug());
    }
}
