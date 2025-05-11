<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Round;
use Countable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * @return BelongsTo<Tournament, Division>
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * @return BelongsTo<MatchGroup, Division>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(MatchGroup::class, 'group_id');
    }

    /**
     * @return HasMany<Matchup, Division>
     */
    public function matches(): HasMany
    {
        return $this->hasMany(Matchup::class);
    }

    /**
     * @return BelongsToMany<PrizePool, Division, DivisionPrize, 'pool'>
     */
    public function prizes(): BelongsToMany
    {
        return $this->belongsToMany(PrizePool::class, DivisionPrize::class, 'division_id', 'prize_id')
            ->withPivot(['amount', 'medal'])
            ->as('pool');
    }

    public function hasByes(): Attribute
    {
        return Attribute::get(fn (): bool => $this->attr?->has_byes ?: false);
    }

    public function getRoundLabel(int $current, int|Countable $total): Round
    {
        if ($this->has_byes && $current === 0) {
            return Round::Preliminary;
        }

        if ($total instanceof Countable) {
            $total = count($total);
        }

        if (! $this->has_byes) {
            $total++;
            $current++;
        }

        return Round::from($total - $current);
    }
}
