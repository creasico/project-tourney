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
        return new ArrayIterator([
            'blue' => $this->blue,
            'red' => $this->red,
        ]);
    }

    public function toArray(): array
    {
        return array_filter([$this->blue, $this->red]);
    }

    public function have(string $id)
    {
        if ($this->isBye()) {
            return $this->blue->id === $id;
        }

        $ids = array_map(fn (Person|Party $p) => $p->id, [...$this]);

        return in_array($id, $ids, true);
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
