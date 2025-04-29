<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    /** @use HasFactory<\Database\Factories\DivisionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'attr' => AsArrayObject::class,
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MatchGroup::class, 'group_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(Matchup::class);
    }

    public function prizes(): BelongsToMany
    {
        return $this->belongsToMany(PrizePool::class, DivisionPrize::class, 'division_id', 'prize_id')
            ->withPivot(['amount', 'medal'])
            ->as('pool');
    }
}
