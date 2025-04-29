<?php

namespace App\Support;

use ArrayIterator;
use IteratorAggregate;

final class Sliced implements IteratorAggregate
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
}
