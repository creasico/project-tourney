<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TournamentDivision extends Pivot
{
    protected $table = 'tournament_divisions';

    protected function casts(): array
    {
        return [
            'attr' => AsArrayObject::class,
            'division' => 'integer',
        ];
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DivisionMatch::class);
    }
}
