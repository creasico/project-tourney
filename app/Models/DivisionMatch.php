<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DivisionMatch extends Model
{
    /** @use HasFactory<\Database\Factories\DivisionMatchFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'attr' => AsArrayObject::class,
            'gender' => Gender::class,
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(TournamentDivision::class);
    }

    public function prizes(): BelongsToMany
    {
        return $this->belongsToMany(PrizePool::class, DivisionPrize::class)
            ->withPivot(['amount', 'medal'])
            ->as('pool');
    }
}
