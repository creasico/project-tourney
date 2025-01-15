<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum TournamentStatus: int
{
    use ArrayableEnum, OptionableEnum;

    case Scheduled = 0;

    case OnGoing = 1;

    case Finished = 2;

    public function label(): string
    {
        return trans('tournament.status.'.str($this->name)->slug());
    }

    public function isScheduled(): bool
    {
        return $this === self::Scheduled;
    }

    public function isOnGoing(): bool
    {
        return $this === self::OnGoing;
    }

    public function isFinished(): bool
    {
        return $this === self::Finished;
    }
}
