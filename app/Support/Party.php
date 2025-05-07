<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\MatchSide;

final class Party
{
    public function __construct(
        public readonly string $id,
        public readonly MatchSide $side,
    ) {}
}
