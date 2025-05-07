<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\MatchSide;
use Illuminate\Support\Str;

final class Matchup
{
    public readonly string $id;

    public readonly bool $isBye;

    public ?string $nextId = null;

    public ?MatchSide $nextSide = null;

    public function __construct(
        public readonly Sided $party,
        public int $index,
        public int $round,
    ) {
        $this->id = strtolower((string) Str::ulid());
        $this->isBye = $party->isBye();

        if ($party->isBye()) {
            $this->round++;
        }
    }
}
