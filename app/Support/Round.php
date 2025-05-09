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

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo(): array
    {
        return [
            'participants' => collect($this->participants)
                ->mapWithKeys(fn ($p) => [
                    $p->id => sprintf('%s(%s)', $p::class, $p->side?->value ?? 'none'),
                ])
                ->all(),

            'matches' => collect(value: $this->matches)
                ->map(fn (Matchup $m) => $m->__debugInfo())
                ->all(),
        ];
    }

    public function contains(Matchup|Party $match)
    {
        if (empty($this->matches)) {
            return false;
        }

        return Arr::first($this->matches, fn ($m) => $m->id === $match->id) !== null;
    }

    public function isEmpty()
    {
        return empty($this->matches) && count($this->participants) === 1;
    }
}
