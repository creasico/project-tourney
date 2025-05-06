<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TournamentLevel;
use App\Events\ParticipantDisqualified;
use App\Events\ParticipantKnockedOff;
use App\Events\ParticipantVerified;
use App\Events\TournamentFinished;
use App\Events\TournamentStarted;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    /** @use HasFactory<\Database\Factories\TournamentFactory> */
    use HasFactory, HasUlids;

    use Helpers\WithTimelineStatus;

    protected $timelineColumns = [
        'draft' => 'published_at',
        'start' => 'start_date',
        'finish' => 'finish_date',
    ];

    protected $timelineEvents = [
        'start' => TournamentStarted::class,
        'finish' => TournamentFinished::class,
    ];

    protected function casts(): array
    {
        return [
            'level' => TournamentLevel::class,
            'attr' => AsArrayObject::class,
            'start_date' => 'immutable_date',
            'finish_date' => 'immutable_date',
            'published_at' => 'immutable_datetime',
        ];
    }

    public static function createAsDraft(
        string $title,
        int $level,
        string $startDate,
        ?string $finishDate = null,
        ?string $description = null
    ): ?static {
        $model = new static([
            'title' => $title,
            'description' => $description,
            'level' => $level,
            'start_date' => $startDate,
            'finish_date' => $finishDate,
            'published_at' => null,
        ]);

        return $model->save() ? $model->fresh() : null;
    }

    /**
     * @return HasMany<Matchup, Tournament>
     */
    public function matches(): HasMany
    {
        return $this->hasMany(Matchup::class);
    }

    /**
     * @return HasMany<MatchGroup, Tournament>
     */
    public function groups(): HasMany
    {
        return $this->hasMany(MatchGroup::class);
    }

    /**
     * @return HasMany<Division, Tournament>
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * @return BelongsToMany<Classification, Tournament, MatchGroup, 'group'>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classification::class, MatchGroup::class, relatedPivotKey: 'class_id')
            ->withPivot(['id', 'division', 'bye', 'attr'])
            ->as('group');
    }

    /**
     * @return BelongsToMany<Classification, Tournament, MatchGroup, 'group'>
     */
    public function withClassifiedAthletes()
    {
        /** @param HasMany|Builders\PersonBuilder $query */
        return $this->classes()->with([
            'athletes' => fn ($query) => $query->haveParticipate($this),
        ]);
    }

    /**
     * @return BelongsToMany<Person, Tournament, Participation, 'participation'>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, Participation::class, relatedPivotKey: 'participant_id')
            ->withPivot([
                'match_id', 'rank_number', 'draw_number', 'medal',
                'disqualification_reason', 'disqualified_at',
                'verified_at', 'knocked_at',
            ])
            ->as('participation');
    }

    /**
     * @return BelongsToMany<Person, Tournament, Participation, 'participation'>
     */
    public function verifiedParticipants()
    {
        return $this->participants()->wherePivotNotNull('verified_at');
    }

    /**
     * @return BelongsToMany<Person, Tournament, Participation, 'participation'>
     */
    public function unverifiedParticipants()
    {
        return $this->participants()->wherePivotNull('verified_at');
    }

    public function isPublished(): Attribute
    {
        return Attribute::get(
            fn () => $this->published_at?->startOfDay()->lt(now()->endOfDay()) ?: false
        );
    }

    public function disqualify(Person $participant, ?string $reason = null)
    {
        $disqualified = $this->participants()->updateExistingPivot($participant, [
            'disqualification_reason' => $reason,
            'disqualified_at' => $this->freshTimestamp(),
        ]);

        event(new ParticipantDisqualified($participant, $this, $reason));

        return $disqualified;
    }

    public function verify(Person $participant, ?string $reason = null)
    {
        $verified = $this->participants()->updateExistingPivot($participant, [
            'verified_at' => $this->freshTimestamp(),
        ]);

        event(new ParticipantVerified($participant, $this));

        return $verified;
    }

    public function knockOff(Person $participant, ?string $reason = null)
    {
        $knocked = $this->participants()->updateExistingPivot($participant, [
            'knocked_at' => $this->freshTimestamp(),
        ]);

        event(new ParticipantKnockedOff($participant, $this));

        return $knocked;
    }
}
