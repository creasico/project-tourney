<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum MatchSide: string
{
    use ArrayableEnum, OptionableEnum;

    case Blue = 'blue';

    case Red = 'red';

    public function label(): string
    {
        return trans('match.side.'.$this->value);
    }
}
