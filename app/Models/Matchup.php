<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MatchSide;
use App\Enums\PartyStatus;
use App\Support\Athlete;
use App\Support\Sided;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * @return BelongsTo<Tournament, Matchup>
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * @return BelongsTo<Division, Matchup>
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * @return HasMany<Participation, Matchup>
     */
    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class, 'match_id');
    }

    /**
     * @return BelongsToMany<Person, Matchup, MatchParty, 'party'>
     */
    public function athletes(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->belongsToMany(Person::class, MatchParty::class, 'match_id', 'participant_id')
            ->withPivot(['side', 'status'])
            ->as('party');
    }

    public function addAthletes(Sided $sided, Tournament $tournament): void
    {
        $this->addAthlete(
            athlete: $sided->blue,
            tournament: $tournament,
            side: MatchSide::Blue,
        );

        if ($sided->red) {
            $this->addAthlete(
                athlete: $sided->red,
                tournament: $tournament,
                side: MatchSide::Red,
            );
        }
    }

    public function addAthlete(
        Person $athlete,
        Tournament $tournament,
        MatchSide $side,
        PartyStatus $status = PartyStatus::Queue,
    ): void {
        $this->athletes()->attach($athlete, [
            'side' => $side,
            'status' => $status,
        ]);

        $tournament->participants()->updateExistingPivot($athlete, [
            'match_id' => $this->id,
        ]);
    }

    /**
     * @return BelongsToMany<Person, Matchup, MatchParty, 'party'>
     */
    private function whereSide(MatchSide $side): BelongsToMany|Builders\PersonBuilder
    {
        return $this->athletes()->wherePivot('side', $side);
    }

    /**
     * @return BelongsToMany<Person, Matchup, MatchParty, 'party'>
     */
    public function blue(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->whereSide(MatchSide::Blue);
    }

    /**
     * @return BelongsToMany<Person, Matchup, MatchParty, 'party'>
     */
    public function red(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->whereSide(MatchSide::Red);
    }

    /**
     * @return BelongsToMany<Person, Matchup, MatchParty, 'party'>
     */
    public function winning(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->athletes()->wherePivot('status', PartyStatus::Win);
    }

    /**
     * @return BelongsToMany<Person, Matchup, MatchParty, 'party'>
     */
    public function losing(): BelongsToMany|Builders\PersonBuilder
    {
        return $this->athletes()->wherePivot('status', PartyStatus::Lose);
    }

    /**
     * @return BelongsTo<Matchup, Matchup>
     */
    public function next(): BelongsTo
    {
        return $this->belongsTo(Matchup::class, 'next_id');
    }

    /**
     * @return HasMany<Matchup, Matchup>
     */
    public function prevs(): HasMany
    {
        return $this->hasMany(Matchup::class, 'next_id');
    }

    public function winner(): Attribute
    {
        return Attribute::get(fn (): ?Person => $this->winning->first());
    }

    public function blueSide(): Attribute
    {
        return Attribute::get(function (): ?Athlete {
            if ($person = $this->blue->first()) {
                return new Athlete(
                    $person,
                    $this->participations->where('participant_id', $person->id)->first(),
                );
            }

            $prev = $this->prevs
                ->where('next_side', MatchSide::Blue)
                ->first();

            if ($prev) {
                return new Athlete($prev);
            }

            return null;
        });
    }

    public function redSide(): Attribute
    {
        return Attribute::get(function (): ?Athlete {
            if ($person = $this->red->first()) {
                return new Athlete(
                    $person,
                    $this->participations->where('participant_id', $person->id)->first(),
                );
            }

            $prev = $this->prevs
                ->where('next_side', MatchSide::Red)
                ->first();

            if ($prev) {
                return new Athlete($prev);
            }

            return null;
        });
    }
}
