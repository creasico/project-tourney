<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classification extends Model
{
    /** @use HasFactory<\Database\Factories\ClassificationFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, TournamentDivision::class)
            ->withPivot(['label', 'division', 'attr'])
            ->as('division');
    }
}
