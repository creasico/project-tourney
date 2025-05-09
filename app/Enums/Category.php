<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum Category: int implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    case Match = 1;

    case Single = 2;

    case Double = 3;

    case Group = 4;

    public function getLabel(): string
    {
        return trans('category.'.str($this->name)->slug());
    }
}
