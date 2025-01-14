<?php

namespace App\Models;

use App\Enums\Reward as RewardEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Participation extends Pivot
{
    protected $table = 'participations';

    protected function casts(): array
    {
        return [
            'rank_number' => 'integer',
            'draw_number' => 'integer',
            'medal' => RewardEnum::class,
            'disqualified_at' => 'immutable_datetime',
            'knocked_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
        ];
    }

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'class_id');
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
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
            fn () => $this->verified_at && now()->greaterThan($this->verified_at)
        );
    }
}
