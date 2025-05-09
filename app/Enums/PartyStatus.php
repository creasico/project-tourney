<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum PartyStatus: int implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    case Queue = 0;

    case Win = 1;

    case Lose = 2;

    case Draw = 3;

    public function getLabel(): string
    {
        return trans('match.party_status.'.str($this->name)->slug());
    }

    public function isQueue(): bool
    {
        return $this === self::Queue;
    }

    public function isWin(): bool
    {
        return $this === self::Win;
    }

    public function isLose(): bool
    {
        return $this === self::Lose;
    }

    public function isDraw(): bool
    {
        return $this === self::Draw;
    }
}
