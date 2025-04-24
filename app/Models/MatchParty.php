<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MatchSide;
use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MatchParty extends Pivot
{
    protected $table = 'match_parties';

    protected function casts(): array
    {
        return [
            'side' => MatchSide::class,
            'status' => MatchStatus::class,
        ];
    }
}
