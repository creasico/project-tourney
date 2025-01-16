<?php

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
            'round' => 'integer',
            'status' => MatchStatus::class,
        ];
    }
}
