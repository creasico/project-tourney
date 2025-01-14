<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\ParticipantRole;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Participant extends Model
{
    /** @use HasFactory<\Database\Factories\ParticipantFactory> */
    use HasFactory, HasUlids;

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

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'class_id');
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, Participation::class)
            ->withPivot([
                'rank_number', 'draw_number', 'medal',
                'disqualification_reason', 'disqualified_at',
                'verified_at', 'knocked_at',
            ])
            ->as('participate');
    }
}
