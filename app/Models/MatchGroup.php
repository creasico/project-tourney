<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MatchBye;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'class_id');
    }

    public function divisions()
    {
        return $this->hasMany(Division::class, 'group_id');
    }
}
