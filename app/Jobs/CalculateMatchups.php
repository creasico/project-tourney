<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\MatchSide;
use App\Events\MatchupInitialized;
use App\Models\Person;
use App\Models\Tournament;
use App\Support\ClassifiedAthletes;
use App\Support\Matchup;
use App\Support\Party;
use App\Support\Round;
use App\Support\Sided;
use App\Support\Sliced;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CalculateMatchups implements ShouldBeUnique, ShouldQueue
{
    use Batchable, Queueable, SerializesModels;
    use ClassifiedAthletes, FailsHelper;

    public function __construct(
        protected Tournament $tournament,
        protected string $classId,
    ) {}

    public function uniqueId(): string
    {
        return $this->tournament->getKey().':'.$this->classId;
    }

    /**
     * @codeCoverageIgnore
     */
    private function context(): array
    {
        return [
            'tournament_id' => $this->tournament->id,
            'class_id' => $this->classId,
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $class = $this->classifiedAthletes();

        $matches = $this->tournament->matches()
            ->where('class_id', $class->getKey());

        if ($matches->exists()) {
            $matches->delete();
            $class->group->divisions()->delete();
        }

        DB::transaction(function () use ($class) {
            $divisions = $this->divide(
                athletes: $this->prepareAthletes($class->athletes),
                division: $class->group->division,
                count: $athletesCount = $class->athletes->count()
            );

            $divided = count($divisions) > 1;

            foreach ($divisions as $i => $participants) {
                $label = $class->display;

                if ($divided) {
                    $no = $i + 1;
                    $label .= " {$no}";
                }

                $division = $class->group->divisions()->create([
                    'label' => $label,
                    'tournament_id' => $this->tournament->id,
                ]);

                /** @var array<string, string> */
                $matches = [];
                $rounds = $this->createRounds($participants);

                krsort($rounds);

                foreach ($rounds as $r => $round) {
                    foreach ($round->matches as $match) {
                        $attrs = [
                            'class_id' => $class->id,
                            'division_id' => $division->id,
                            'round_number' => $r,
                            'is_bye' => $match->isBye,
                            'order' => count($matches),
                            'attr' => [
                                'index' => $match->index,
                            ],
                        ];

                        if ($match->nextId && array_key_exists($match->nextId, $matches)) {
                            $attrs['next_id'] = $matches[$match->nextId];
                            // $attrs['next_side'] = $match->nextSide;
                        }

                        $matchup = $this->tournament->matches()->create($attrs);

                        foreach ($match->party as $side => $party) {
                            if ($party instanceof Person) {
                                $matchup->addAthlete(
                                    $party,
                                    $this->tournament,
                                    MatchSide::from($side),
                                );
                            }
                        }

                        $matches[$match->id] = $matchup->getKey();
                    }
                }

                event(new MatchupInitialized(
                    tournament: $this->tournament,
                    classId: $class->id,
                    divisionId: $division->id,
                    matches: $matches,
                ));
            }
        });
    }

    /**
     * @param  list<Person>  $participants
     * @param  list<Matchup>  $matches
     * @return list<Round>
     */
    public function createRounds(array $participants, array $matches = []): array
    {
        /** @var list<Round> */
        $rounds = [];
        $items = $participants;
        $next = true;
        $r = 0;

        while ($next) {
            if (! array_key_exists($r, $rounds)) {
                // In case of the current iteration is actually already exists
                // due to match relocation from previous match calculation.
                $rounds[$r] = new Round($r, $items);
            }

            if ($r > 0) {
                // On second round onward, we might have some participant already
                // regitered from previous round and in current iteration all
                // we need is take them as the basis for creating matches.
                $items = $rounds[$r]->participants;
                $matches = $rounds[$r]->matches;
            }

            if (count($items) === 1) {
                // When this round only contains of 1 participant, meaning we've
                // already reacing the final round, no need further iteration.
                array_pop($rounds);

                break;
            }

            $sides = $r === 0
                ? $this->determineSide($items)
                : $this->assignSide($items);

            $gap = 0;
            $byes = [];

            foreach ($this->createMatches($sides, $r, $matches) as $match) {
                if (! array_key_exists($match->round, $rounds)) {
                    $rounds[$match->round] = new Round($match->round);
                }

                if ($rounds[$match->round]->contains($match)) {
                    continue;
                }

                $lastBye = false;

                if ($match->isBye) {
                    $gap++;
                    $byes[] = $match->id;
                } else {
                    $lastBye = end($byes);
                    $match->gap = $gap;
                    $gap = 0;
                    $byes = [];
                }

                $rounds[$match->round]->matches[] = $match;

                // Once we've done registering the match to its desire round,
                // now we should registers the match as a participant of the
                // next round.
                $nextRound = $match->round + 1;

                if (! array_key_exists($nextRound, $rounds)) {
                    $rounds[$nextRound] = new Round($nextRound);
                }

                $party = new Party($match->id, $match->nextSide);

                if ($lastBye && ! empty($rounds[$nextRound]->matches)) {
                    foreach ($rounds[$nextRound]->matches as $bm => $byeMatch) {
                        if ($lastBye === $byeMatch->id && ! $byeMatch->party->red) {
                            $rounds[$nextRound]->matches[$bm]->party->red = $party;

                            break;
                        }
                    }
                } else {
                    $rounds[$nextRound]->participants[] = $party;
                }
            }

            $r++;

            unset($nextRound, $match, $sides);
        }

        return $rounds;
    }

    /**
     * @param  list<Sided>  $items
     * @param  list<Matchup>  $matches
     * @param  list<int>  $byes
     * @return list<Matchup>
     */
    public function createMatches(
        array $items,
        int $round,
        array &$matches = [],
        array &$byes = [],
    ): array {
        if (empty($items)) {
            return $matches;
        }

        $parties = $items;
        $half = (int) floor(count($parties) / 2);
        $chunks = $half >= 2 ? [
            array_slice($parties, 0, $half),
            array_slice($parties, $half),
        ] : [$parties, []];

        if (empty($byes)) {
            $byes = $this->collectByes($parties);
        }

        $hasByes = count($byes) > 0;

        foreach ($chunks as $c => $parties) {
            $total = count($parties);

            // Recusively calculate when the number of `parties` on `round` 0 is 5 or more
            if ($round === 0 && $total > 5) {
                $this->createMatches($parties, $round, $matches, $byes);

                continue;
            }

            // Retrieve the last match on the previous chunk
            $prevMatch = end($matches) ?: null;

            // Reassure that the current chunk has bye mathces
            if (! $hasByes && $prevMatch?->party->isBye() === false) {
                $hasByes = count($this->collectByes($parties)) > 0;
            }

            foreach ($parties as $p => $party) {
                // Get the match index based on number of already registered matches
                $index = count($matches);

                // Determine whether this one is a bye match based on previous iteration
                $bye = $hasByes && $index <= end($byes);

                $match = new Matchup($party, $index, $round, $bye);

                if ($p > 0 && $matches[$index - 1]->party->isBye()) {
                    $hasByes = $match->isBye = false;
                }

                $isLast = $total > 1 && ($p + 1) === $total;

                // Force next side to be `red` when it was the last match in
                // the split or the previous registered match was a bye match.
                if ($isLast || $prevMatch?->party->isBye()) {
                    $match->nextSide = MatchSide::Red;
                }

                $matches[] = $match;
            }
        }

        return $matches;
    }

    /**
     * Evenly distribute athletes for each matchup divisions.
     *
     * @param  list<Person>  $athletes
     * @return array<int, list<Person>>
     */
    public function divide(array $athletes, int $division, int $count): array
    {
        $division = $division > 0 ? $division : $count;
        $chunks = array_chunk($athletes, $division);

        if (
            $count % $division === 0 ||
            count(end($chunks)) > floor($division / 2)
        ) {
            return $chunks;
        }

        $last = array_merge(...array_splice($chunks, -2));
        $lastCount = count($last);
        $div = ceil($lastCount / 2);

        if ($div <= 2) {
            $chunks[] = $last;

            return $chunks;
        }

        array_push($chunks, ...array_chunk($last, (int) $div));

        return $chunks;
    }

    /**
     * @param  Collection<int, Person>  $athletes
     * @return list<Person>
     */
    public function prepareAthletes(Collection $athletes): array
    {
        $groupedAthletes = $athletes->groupBy('continent_id')
            ->map(fn ($items) => $items->all())
            ->all();

        $result = $this->shuffle(array_values($groupedAthletes));
        $count = count($result);

        // At this stage we might still find some athletes facing their comrade
        // in the matchup, now we need to ensure that they will be resuffled.
        foreach ($result as $r => $row) {
            // Skip the first iteration or when the previous iteration has same
            // continent id as the current one.
            if ($r === 0 || $row->continent_id !== $result[$r - 1]->continent_id) {
                continue;
            }

            // Let's traverse the result backward and find an opponent for the
            // athlets from another continent by checking on each iteration
            // doesn't come from the same continent.
            for ($i = $count - 1; $i >= 0; $i--) {
                // Skip iteration on the same index.
                if (in_array($i, [$r, $r - 1], true)) {
                    continue;
                }

                $range = array_reduce(
                    range($i - 1, $i + 1),
                    function (array $range, int $i) use ($result): array {
                        if (array_key_exists($i, $result)) {
                            $range[] = $result[$i]->continent_id;
                        }

                        return $range;
                    },
                    [],
                );

                if (in_array($row->continent_id, $range, true)) {
                    continue;
                }

                $result[$r] = $result[$i];
                $result[$i] = $row;

                break;
            }
        }

        return $result;
    }

    /**
     * @param  list<Party|Person>  $items
     * @return list<Sided>
     */
    private function assignSide(array $items): array
    {
        $result = [];

        foreach ($items as $i => $item) {
            if ($i === 0) {
                $result[] = new Sided($item);

                continue;
            }

            if ($item->side?->isBlue() || $item instanceof Person) {
                $result[] = new Sided($item);

                continue;
            }

            $last = count($result) - 1;
            $result[$last] = new Sided($result[$last]->blue, $item);
        }

        return $result;
    }

    /**
     * @param  list<Person>  $items
     * @return list<Sided>
     */
    public function determineSide(array $items): array
    {
        // First, let's split the items in floored half value.
        $half = (int) floor(count($items) / 2);
        $slices[] = $this->slice($items, $half);

        while ($half > 0) {
            $half = (int) floor($half / 2);

            $slices = array_reduce($slices, function (array $slices, Sliced $slice) use ($half): array {
                if (count($slice->upper) === 1 && count($slice->lower) === 1) {
                    $slices[] = $slice;

                    return $slices;
                }

                // On last chunk iteration that has 2 upper and 1 lower
                // Swap their participant to the correct allocation.
                if (count($slice->upper) === 2 && count($slice->lower) === 1) {
                    array_unshift($slice->lower, array_pop($slice->upper));
                }

                foreach ($slice as $side) {
                    $slices[] = count($side) > 1
                        ? $this->slice($side, $half)
                        : new Sliced($side);
                }

                return $slices;
            }, []);
        }

        return array_reduce($slices, function ($result, Sliced $slice) {
            if (empty($slice->upper) && count($slice->lower) === 1) {
                $slice->upper[] = array_pop($slice->lower);
            }

            assert(count($slice->upper) === 1, 'Upper slice should not be empty');

            $result[] = new Sided(
                $slice->upper[0],
                $slice->lower[0] ?? null
            );

            return $result;
        }, []);
    }

    /**
     * Recursive method to find opponents for each athletes from another continents
     *
     * @param  array<int, list<Person>>  $groups  Grouped athletes by continent
     * @param  list<Person>  $items  Initial value
     * @return list<Person>
     */
    private function shuffle(array $groups, array &$items = []): array
    {
        // First, we need to sort the group based on its number of athletes
        // from biggest to smallest one.
        usort($groups, fn ($a, $b) => count($b) <=> count($a));

        // Second, takes the biggest one off from the list for the initial loop.
        $athletes = array_shift($groups);

        // As the iteration goes, we might find that no groups left in the list.
        // At this state we can assume that $athletes already populated, all we
        // need to do is take the prevous outputs as the next opponents.
        if (empty($groups) && ! empty($athletes)) {
            // Let's retrieve all existing athletes from another continents.
            $others = array_filter(
                $items,
                fn ($row) => $row['continent_id'] !== $athletes[0]['continent_id'],
                ARRAY_FILTER_USE_BOTH,
            );

            // Takes the others to match the current athletes.
            $others = array_slice($others, 0, count($athletes), true);
            $opponents = [];

            krsort($others);

            foreach (array_keys($others) as $offset) {
                $opponents[] = array_splice($items, $offset, 1)[0];
            }

            $groups[] = $opponents;
        }

        // Ensure the groups has some athletes left.
        $groups = array_filter(
            array_values($groups),
            fn ($c) => ! empty($c),
        );

        $i = 0;

        // Loop the base athletes to find its opponents from another groups.
        while (! empty($athletes)) {
            $items[] = array_shift($athletes);

            if (! isset($groups[$i]) || ! is_array($groups[$i])) {
                break;
            }

            $items[] = array_shift($groups[$i]);

            $i++;
        }

        // Here we might find there's some athletes left due to in the previous
        // loop we can't find any opponents for them, now let's put them in as
        // the next opponents for the remaining loops.
        if (! empty($athletes)) {
            $groups[] = $athletes;
        }

        // Recursively iterate until there's no opponents left behind.
        if (! empty($groups)) {
            return $this->shuffle($groups, $items);
        }

        return $items;
    }

    /**
     * @param  list<Sided>  $items
     * @return list<int>
     */
    private function collectByes(array $items)
    {
        $byes = [];

        foreach ($items as $i => $item) {
            if ($item->isBye()) {
                $byes[] = $i;
            }
        }

        return $byes;
    }

    /**
     * Split the items into upper and lower section.
     *
     * @param  list<Person>  $name
     */
    private function slice(array $items, int $slice): Sliced
    {
        if (floor(count($items) / 2) > $slice) {
            $slice++;
        }

        return new Sliced(
            upper: array_slice($items, 0, $slice),
            lower: array_slice($items, $slice),
        );
    }
}
