<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\MatchSide;
use Illuminate\Support\Str;

/**
 * State class to hold matchup information before get stored in the database.
 */
final class Matchup
{
    public readonly string $id;

    public bool $isBye;

    public bool $isHidden = false;

    /**
     * Match ID on the next round.
     */
    public ?string $nextId = null;

    /**
     * Match side on the next round.
     */
    public MatchSide $nextSide;

    public function __construct(
        public readonly Sided $party,
        public readonly int $index,
        public int $round,
        bool $bye = false,
    ) {
        $this->id = strtolower((string) Str::ulid());
        $this->isBye = $party->isBye() || $bye;

        // Hide and relocate this match to next round when it was a bye match.
        if ($this->isBye) {
            $this->round++;
            $this->isHidden = true;
        }

        // Determine which side the winner of this match would be on the next round.
        $this->nextSide = $index % 2 === 0 ? MatchSide::Blue : MatchSide::Red;
    }
}
