<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum MatchSide: string implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    case Blue = 'blue';

    case Red = 'red';

    public function getLabel(): string
    {
        return trans('match.side.'.$this->value);
    }

    public function isBlue(): bool
    {
        return $this === self::Blue;
    }

    public function isRed(): bool
    {
        return $this === self::Red;
    }
}
