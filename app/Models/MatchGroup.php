<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MatchBye;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MatchGroup extends Pivot
{
    protected $table = 'match_groups';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'attr' => AsArrayObject::class,
            'division' => 'integer',
            'bye' => MatchBye::class,
        ];
    }

    /**
     * @return BelongsTo<Tournament, MatchGroup>
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * @return BelongsTo<Classification, MatchGroup>
     */
    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'class_id');
    }

    /**
     * @return HasMany<Division, MatchGroup>
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class, 'group_id');
    }
}
