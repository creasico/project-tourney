<?php

namespace App\Models;

use App\Enums\TournamentStatus;
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
            ->withPivot('rank_number', 'draw_number', 'medal', 'disqualified_at', 'knocked_at')
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
            fn () => Carbon::parse($this->start_date)->startOfDay()->lt(now()->endOfDay())
        );
    }

    public function isFinished(): Attribute
    {
        return Attribute::get(
            fn () => Carbon::parse($this->finish_date)->endOfDay()->lt(now()->startOfDay())
        );
    }
}
