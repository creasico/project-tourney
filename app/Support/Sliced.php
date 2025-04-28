<?php

namespace App\Support;

use ArrayIterator;
use Countable;
use IteratorAggregate;

final class Sliced implements Countable, IteratorAggregate
{
    public function __construct(
        public array $upper,
        public array $lower = [],
    ) {}

    public function getIterator(): \Traversable
    {
        return new ArrayIterator([
            $this->upper,
            $this->lower,
        ]);
    }

    public function count(): int
    {
        return count(array_merge(...$this));
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
