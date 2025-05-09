<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    case Male = 'male';

    case Female = 'female';

    public function getLabel(): string
    {
        return trans('app.gender.'.$this->value);
    }
}
