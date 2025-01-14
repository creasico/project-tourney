<?php

namespace App\Models;

use App\Enums\MatchSide;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MatchUp extends Model
{
    /** @use HasFactory<\Database\Factories\MatchUpFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'next_side' => MatchSide::class,
            'round' => 'integer',
            'order' => 'integer',
            'is_bye' => 'boolean',
            'attr' => 'object',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
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

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, MatchHistory::class)
            ->withPivot('side', 'round', 'status')
            ->as('party');
    }

    public function next(): BelongsTo
    {
        return $this->belongsTo(MatchUp::class, 'next_id');
    }
}
