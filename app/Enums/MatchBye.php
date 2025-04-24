<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum MatchBye: string
{
    use ArrayableEnum, OptionableEnum;

    case Up = 'up';

    case Down = 'down';

    public function label(): string
    {
        return trans('match.bye.'.$this->value);
    }

    public function isUp(): bool
    {
        return $this === self::Up;
    }

    public function isDown(): bool
    {
        return $this === self::Down;
    }
}
