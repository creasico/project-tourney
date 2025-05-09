<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum TimelineStatus: int implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    case Draft = 0;

    case Scheduled = 1;

    case Started = 2;

    case Finished = 3;

    public function getLabel(): string
    {
        return trans('app.timeline_status.'.str($this->name)->slug());
    }

    public function isDraft(): bool
    {
        return $this === self::Draft;
    }

    public function isScheduled(): bool
    {
        return $this === self::Scheduled;
    }

    public function isStarted(): bool
    {
        return $this === self::Started;
    }

    public function isFinished(): bool
    {
        return $this === self::Finished;
    }
}
