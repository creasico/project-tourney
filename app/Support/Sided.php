<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Person;
use Countable;

final class Sided implements Countable
{
    public function __construct(
        public readonly Person $blue,
        public readonly ?Person $red = null,
    ) {}

    public function count(): int
    {
        return $this->red === null ? 1 : 2;
    }

    public function isBye(): bool
    {
        return $this->count() === 1;
    }
}
