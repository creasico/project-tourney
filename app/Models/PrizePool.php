<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PrizePool extends Model
{
    /** @use HasFactory<\Database\Factories\PrizePoolFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function prizes(): BelongsToMany
    {
        return $this->belongsToMany(DivisionMatch::class, DivisionPrize::class, 'prize_id', 'division_id')
            ->withPivot(['amount', 'medal'])
            ->as('pool');
    }
}
