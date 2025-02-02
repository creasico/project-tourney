<?php

namespace App\Models;

use App\Enums\ClassificationTerm;
use Illuminate\Database\Eloquent\Builder;
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
            'term' => ClassificationTerm::class,
        ];
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, TournamentDivision::class)
            ->withPivot(['division', 'attr'])
            ->as('division');
    }

    public function scopeOnlyAges(Builder $query)
    {
        return $query->where('term', ClassificationTerm::Age);
    }

    public function scopeOnlyWeight(Builder $query)
    {
        return $query->where('term', ClassificationTerm::Weight);
    }
}
