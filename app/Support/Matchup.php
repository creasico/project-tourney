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

    public ?string $nextId = null;

    public ?MatchSide $nextSide = null;

    public bool $isHidden = false;

    public function __construct(
        public readonly Sided $party,
        public int $index,
        public int $round,
    ) {
        $this->id = strtolower((string) Str::ulid());
        $this->isBye = $party->isBye();

        if ($party->isBye()) {
            $this->round++;
            $this->isHidden = true;
        }
    }
}
