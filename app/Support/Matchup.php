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

    /**
     * Match ID on the next round.
     */
    public ?string $nextId = null;

    /**
     * Match side on the next round.
     */
    public MatchSide $nextSide;

    /**
     * Gap between this match and the previous match in the same round.
     */
    public int $gap = 0;

    public int $order = 0;

    public function __construct(
        public readonly Sided $party,
        public int $index,
        public int $round,
        bool $bye = false,
    ) {
        $this->id = strtolower((string) Str::ulid());
        $this->isBye = $party->isBye() || $bye;

        // Relocate this match to next round when it was a bye match.
        if ($this->isBye) {
            $this->round++;
        }

        // Determine which side the winner of this match would be on the next round.
        $this->nextSide = $this->getNextSide($index);
    }

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo(): array
    {
        return [
            'id' => $this->id,
            'index' => $this->index,
            'order' => $this->order,
            'gap' => $this->gap,
            'round' => $this->round,
            'isBye' => $this->isBye,
            'nextId' => $this->nextId,
            'nextSide' => $this->nextSide->value,
            'party' => collect($this->party)->mapWithKeys(fn ($p) => [
                $p->id => $p::class,
            ])->toArray(),
        ];
    }

    /**
     * Update existing index.
     */
    public function update(int $index): static
    {
        $this->index = $index;
        $this->nextSide = $this->getNextSide($index);

        return $this;
    }

    /**
     * Determine which side the winner of this match would be on the next round.
     */
    public function getNextSide(int $index): MatchSide
    {
        return $index % 2 === 0 ? MatchSide::Blue : MatchSide::Red;
    }
}
