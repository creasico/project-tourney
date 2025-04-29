<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MatchSide;
use App\Enums\PartyStatus;
use App\Support\Sided;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    use Helpers\WithTimelineStatus;

    protected function casts(): array
    {
        return [
            'next_side' => MatchSide::class,
            'party_number' => 'integer',
            'round_number' => 'integer',
            'order' => 'integer',
            'is_bye' => 'boolean',
            'attr' => AsArrayObject::class,
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, MatchParty::class, 'match_id', 'participant_id')
            ->withPivot(['side', 'status'])
            ->as('party');
    }

    public function blue()
    {
        return $this->athletes()->wherePivot('side', MatchSide::Blue);
    }

    public function red()
    {
        return $this->athletes()->wherePivot('side', MatchSide::Red);
    }

    public function winner()
    {
        return $this->athletes()->wherePivot('status', PartyStatus::Win);
    }

    public function loser()
    {
        return $this->athletes()->wherePivot('status', PartyStatus::Lose);
    }

    public function next(): BelongsTo
    {
        return $this->belongsTo(Matchup::class, 'next_id');
    }

    public function addAthletes(Sided $sided, Tournament $tournament)
    {
        $this->athletes()->attach($sided->blue, [
            'side' => MatchSide::Blue,
            'status' => PartyStatus::Queue,
        ]);

        $tournament->participants()->updateExistingPivot($sided->blue, [
            'match_id' => $this->id,
        ]);

        if ($sided->red) {
            $this->athletes()->attach($sided->red, [
                'side' => MatchSide::Red,
                'status' => PartyStatus::Queue,
            ]);

            $tournament->participants()->updateExistingPivot($sided->red, [
                'match_id' => $this->id,
            ]);
        }
    }

    public function isStarted(): Attribute
    {
        return Attribute::get(
            fn () => $this->started_at?->startOfDay()->lt(now()->endOfDay()) ?: false
        );
    }

    public function isFinished(): Attribute
    {
        return Attribute::get(
            fn () => $this->finished_at?->endOfDay()->lt(now()->startOfDay()) ?: false
        );
    }
}
