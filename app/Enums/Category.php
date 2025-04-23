<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum Category: int
{
    use ArrayableEnum, OptionableEnum;

    case Match = 1;

    case Single = 2;

    case Double = 3;

    case Group = 4;

    public function label(): string
    {
        return trans('category.'.str($this->name)->slug());
    }
}
