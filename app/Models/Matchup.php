<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MatchSide;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Matchup extends Model
{
    /** @use HasFactory<\Database\Factories\MatchupFactory> */
    use HasFactory, HasUlids;

    use Helpers\WithClassification;

    protected function casts(): array
    {
        return [
            'next_side' => MatchSide::class,
            'party_number' => 'integer',
            'round_number' => 'integer',
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

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, MatchParty::class, 'match_id', 'participant_id')
            ->withPivot(['side', 'status'])
            ->as('party');
    }

    public function next(): BelongsTo
    {
        return $this->belongsTo(Matchup::class, 'next_id');
    }

    public function attachAthlete(Person $athlete, MatchSide $side)
    {
        $this->athletes()->attach($athlete, [
            'side' => $side,
        ]);
    }
}
