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

    protected static string $builder = Builders\ParticipantBuilder::class;

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

    /**
     * @return BelongsTo|Builders\ClassificationBuilder
     */
    protected function classification(string $field): BelongsTo
    {
        return $this->belongsTo(Classification::class, $field);
    }

    public function weight(): BelongsTo
    {
        return $this->classification('class_weight_id')->onlyWeights();
    }

    public function age(): BelongsTo
    {
        return $this->classification('class_age_id')->onlyAges();
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, Participation::class)
            ->withPivot([
                'rank_number', 'draw_number', 'medal',
                'disqualification_reason', 'disqualified_at',
                'verified_at', 'knocked_at',
            ])
            ->as('participation');
    }
}
