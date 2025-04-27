<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use App\Enums\ParticipantRole;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    /** @use HasFactory<\Database\Factories\PersonFactory> */
    use HasFactory, HasUlids;

    use Helpers\WithClassification;

    protected static string $builder = Builders\PersonBuilder::class;

    protected function casts(): array
    {
        return [
            'role' => ParticipantRole::class,
            'gender' => Gender::class,
        ];
    }

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function credential()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, Participation::class, foreignPivotKey: 'participant_id')
            ->withPivot([
                'match_id', 'rank_number', 'draw_number', 'medal',
                'disqualification_reason', 'disqualified_at',
                'verified_at', 'knocked_at',
            ])
            ->as('participation');
    }

    public function matches(): BelongsToMany
    {
        return $this->belongsToMany(Matchup::class, MatchParty::class, 'participant_id', 'match_id')
            ->withPivot(['side', 'status'])
            ->as('party');
    }
}
