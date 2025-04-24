<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedalPrize;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DivisionPrize extends Pivot
{
    protected $table = 'division_prizes';

    // public $incrementing = true;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'medal' => MedalPrize::class,
        ];
    }
}
