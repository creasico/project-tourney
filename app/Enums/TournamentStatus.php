<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum TournamentStatus: int
{
    use ArrayableEnum, OptionableEnum;

    case Draft = 0;

    case Scheduled = 1;

    case OnGoing = 2;

    case Finished = 3;

    public function label(): string
    {
        return trans('tournament.status.'.str($this->name)->slug());
    }

    public function isDraft(): bool
    {
        return $this === self::Draft;
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
