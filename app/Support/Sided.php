<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Person;
use ArrayIterator;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;

/**
 * State class to hold matchup information while calculating a match.
 *
 * @implements IteratorAggregate<string, Person|Party>
 */
final class Sided implements Arrayable, Countable, IteratorAggregate
{
    public readonly bool $isPerson;

    public function __construct(
        public readonly Person|Party $blue,
        public Person|Party|null $red = null,
    ) {
        $this->isPerson = $blue instanceof Person;
    }

    public function getIterator(): \Traversable
    {
        return new ArrayIterator(array_filter([
            'blue' => $this->blue,
            'red' => $this->red,
        ]));
    }

    public function toArray(): array
    {
        return array_filter([$this->blue, $this->red]);
    }

    public function sum(bool $isBye = false): int
    {
        $items = $this->toArray();

        if ($isBye) {
            return $this->red instanceof Person ? 1 : 2;
        }

        return array_reduce($items, function (int $sum, Party|Person $side) {
            if ($side instanceof Party) {
                return $sum + $side->size;
            }

            return $sum + 1;
        }, 0);
    }

    public function count(): int
    {
        return $this->isBye() ? 1 : 2;
    }

    public function isBye(): bool
    {
        return $this->red === null;
    }
}
