<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchRevision extends Model
{
    /** @use HasFactory<\Database\Factories\MatchRevisionFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(Matchup::class);
    }
}
