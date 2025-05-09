<?php

declare(strict_types=1);

namespace App\Support;

use ArrayIterator;
use IteratorAggregate;

/**
 * State class to hold information while calculating which participants are
 * belongs to which side on each match.
 */
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
