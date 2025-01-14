<?php

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;

enum Gender: string
{
    use ArrayableEnum, OptionableEnum;

    case Male = 'male';

    case Female = 'female';

    public function label(): string
    {
        return trans('person.gender.'.$this->value);
    }
}
