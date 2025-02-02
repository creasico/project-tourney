<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum ClassificationTerm: int
{
    use ArrayableEnum, OptionableEnum;

    case Age = 0;

    case Weight = 1;

    public function label(): string
    {
        return trans('classification.term.'.str($this->name)->slug());
    }
}
