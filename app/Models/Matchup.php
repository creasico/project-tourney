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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class, 'match_id');
    }

    public function athletes(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->belongsToMany(Person::class, MatchParty::class, 'match_id', 'participant_id')
            ->withPivot(['side', 'status'])
            ->as('party');
    }

    public function addAthletes(Sided $sided, Tournament $tournament)
    {
        $this->addAthlete($sided->blue, $tournament, MatchSide::Blue);

        if ($sided->red) {
            $this->addAthlete($sided->red, $tournament, MatchSide::Red);
        }
    }

    public function addAthlete(
        Person $athlete,
        Tournament $tournament,
        MatchSide $side,
        PartyStatus $status = PartyStatus::Queue
    ): void {
        $this->athletes()->attach($athlete, [
            'side' => $side,
            'status' => $status,
        ]);

        $tournament->participants()->updateExistingPivot($athlete, [
            'match_id' => $this->id,
        ]);
    }

    public function blue(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->athletes()->wherePivot('side', MatchSide::Blue);
    }

    public function red(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->athletes()->wherePivot('side', MatchSide::Red);
    }

    public function winner(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->athletes()->wherePivot('status', PartyStatus::Win);
    }

    public function loser(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->athletes()->wherePivot('status', PartyStatus::Lose);
    }

    public function next(): BelongsTo
    {
        return $this->belongsTo(Matchup::class, 'next_id');
    }

    public function prev(): HasOne
    {
        return $this->hasOne(Matchup::class, 'next_id');
    }

    private function participant(string $side): ?Participation
    {
        if ($participant = $this->{$side}) {
            return $this->participations->where('participant_id', $participant->id)->first();
        }

        return null;
    }

    public function blueSide(): Attribute
    {
        return Attribute::get(fn (): ?Person => $this->blue->first());
    }

    public function blueParticipant(): Attribute
    {
        return Attribute::get(fn (): ?Participation => $this->participant('blue_side'));
    }

    public function redSide(): Attribute
    {
        return Attribute::get(fn (): ?Person => $this->red->first());
    }

    public function redParticipant(): Attribute
    {
        return Attribute::get(fn (): ?Participation => $this->participant('red_side'));
    }
}
