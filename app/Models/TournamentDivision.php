<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TournamentDivision extends Pivot
{
    protected $table = 'tournament_divisions';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'attr' => AsArrayObject::class,
            'division' => 'integer',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'class_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DivisionMatch::class);
    }
}
