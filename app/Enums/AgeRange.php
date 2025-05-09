<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\ArrayableEnum;
use App\Support\OptionableEnum;
use Filament\Support\Contracts\HasLabel;

enum AgeRange: int implements HasLabel
{
    use ArrayableEnum, OptionableEnum;

    /**
     * Less than or equal to 10 years old
     */
    case PreEarly = 1;

    /**
     * More than 10 years old up to 12 years old
     */
    case Early = 2;

    /**
     * More than 12 years old up to 14 years old
     */
    case PreJunior = 3;

    /**
     * More than 14 years old up to 17 years old
     */
    case Junior = 4;

    /**
     * More than 17 years old up to 35 years old
     */
    case Senior = 5;

    /**
     * More than 35 years old up to 45 years old (usually in a separate event)
     */
    case MasterI = 6;

    /**
     * More than 45 years old and above (usually in a separate event)
     */
    case MasterII = 7;

    public function getLabel(): string
    {
        return trans('classification.age.'.str($this->name)->slug());
    }
}
