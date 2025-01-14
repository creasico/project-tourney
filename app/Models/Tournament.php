<?php

namespace App\Models;

use App\Enums\TournamentLevel;
use App\Enums\TournamentStatus;
use App\Events\ParticipantDisqualified;
use App\Events\ParticipantKnockedOff;
use App\Events\ParticipantVerified;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Tournament extends Model
{
    /** @use HasFactory<\Database\Factories\TournamentFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'level' => TournamentLevel::class,
            'attr' => AsArrayObject::class,
            'start_date' => 'immutable_date',
            'finish_date' => 'immutable_date',
        ];
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchUp::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, Participation::class)
            ->withPivot([
                'rank_number', 'draw_number', 'medal',
                'disqualification_reason', 'disqualified_at',
                'verified_at', 'knocked_at',
            ])
            ->as('participation');
    }

    public function status(): Attribute
    {
        return Attribute::get(function () {
            if ($this->is_finished) {
                return TournamentStatus::Finished;
            }

            if ($this->is_started) {
                return TournamentStatus::OnGoing;
            }

            return TournamentStatus::Scheduled;
        });
    }

    public function isStarted(): Attribute
    {
        return Attribute::get(
            fn () => $this->start_date?->startOfDay()->lt(now()->endOfDay())
        );
    }

    public function isFinished(): Attribute
    {
        return Attribute::get(
            fn () => $this->finish_date?->endOfDay()->lt(now()->startOfDay())
        );
    }

    public function dateLabel(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->finish_date) {
                return Carbon::parse($this->start_date)->getCalendarFormats();
            }

        });
    }

    public function disqualify(Participant $participant, ?string $reason = null)
    {
        $disqualified = $this->participants()->updateExistingPivot($participant, [
            'disqualification_reason' => $reason,
            'disqualified_at' => $this->freshTimestamp(),
        ]);

        event(new ParticipantDisqualified($participant, $this, $reason));

        return $disqualified;
    }

    public function verify(Participant $participant, ?string $reason = null)
    {
        $verified = $this->participants()->updateExistingPivot($participant, [
            'verified_at' => $this->freshTimestamp(),
        ]);

        event(new ParticipantVerified($participant, $this));

        return $verified;
    }

    public function knockOff(Participant $participant, ?string $reason = null)
    {
        $knocked = $this->participants()->updateExistingPivot($participant, [
            'knocked_at' => $this->freshTimestamp(),
        ]);

        event(new ParticipantKnockedOff($participant, $this));

        return $knocked;
    }
}
