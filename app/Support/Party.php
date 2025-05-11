<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\MatchSide;
use Illuminate\Contracts\Support\Arrayable;

/**
 * State class to hold participant information while calculating matches on each round.
 */
final class Party implements Arrayable
{
    public function __construct(
        public readonly string $id,
        public readonly MatchSide $side,
        public readonly int $size = 1,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'side' => $this->side->value,
            'size' => $this->size,
        ];
    }
}
