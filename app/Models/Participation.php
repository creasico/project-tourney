<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedalPrize;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Participation extends Pivot
{
    use Helpers\WithClassification;

    protected $table = 'participations';

    protected function casts(): array
    {
        return [
            'rank_number' => 'integer',
            'draw_number' => 'integer',
            'medal' => MedalPrize::class,
            'disqualified_at' => 'immutable_datetime',
            'knocked_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Person, Participation>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'participant_id');
    }

    /**
     * @return BelongsTo<Matchup, Participation>
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(Matchup::class, 'match_id');
    }

    public function isDisqualified(): Attribute
    {
        return Attribute::get(
            fn () => $this->disqualified_at && now()->greaterThan($this->disqualified_at)
        );
    }

    public function isVerified(): Attribute
    {
        return Attribute::get(
            fn () => $this->verified_at && now()->greaterThan($this->verified_at)
        );
    }

    public function isKnocked(): Attribute
    {
        return Attribute::get(
            fn () => $this->knocked_at && now()->greaterThan($this->knocked_at)
        );
    }
}
