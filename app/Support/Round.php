<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * State class to hold round information while calculating its matches.
 */
final class Round
{
    /**
     * @param  list<Party|\App\Models\Person>  $participants
     * @param  list<Matchup>  $matches
     */
    public function __construct(
        public int $index = 0,
        public array $participants = [],
        public array $matches = [],
    ) {}

    public function lastMatchId()
    {
        $lastMatch = array_filter($this->matches, fn ($match) => $match->id !== null);

        return end($lastMatch)?->id;
    }

    public function reallocate(Sided $sided)
    {
        //
    }

    public function contains(Matchup $party)
    {
        if (empty($this->participants)) {
            return false;
        }

        return Arr::first($this->participants, fn ($p) => $p->id === $party->id) !== null;
    }
}
