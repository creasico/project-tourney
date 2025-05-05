<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\MatchupInitialized;
use App\Models\Tournament;
use App\Support\ClassifiedAthletes;
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

        if ($class->group->divisions()->exists()) {
            $class->group->divisions()->delete();

            $matches = $this->tournament->matches()
                ->where('class_id', $class->getKey());

            if ($matches->exists()) {
                $matches->delete();
            }
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

                foreach ($this->determineSide($participants) as $p => $parties) {
                    $p++;

                    $match = $this->tournament->matches()->create([
                        'division_id' => $division->id,
                        'class_id' => $class->id,
                        'is_bye' => $parties->isBye(),
                        'party_number' => $p,
                        'attr' => [
                            'index' => $p,
                        ],
                    ]);

                    $match->addAthletes($parties, $this->tournament);
                }

                event(new MatchupInitialized(
                    $this->tournament,
                    $class->id,
                    $division->id
                ));
            }
        });
    }

    /**
     * Evenly distribute athletes for each matchup divisions.
     *
     * @param  list<\App\Models\Person>  $athletes
     * @return array<int, list<\App\Models\Person>>
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
     * @param  Collection<int, \App\Models\Person>  $athletes
     * @return list<\App\Models\Person>
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

                if (count($range) === 1) {
                    /** @codeCoverageIgnore */
                    logger()->debug('Invalid range', [
                        'count' => $count,
                        'iterations' => [$r, $i],
                        'range' => $range,
                        'row' => $row->toArray(),
                        'result' => $result,
                    ]);
                }

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
     * @param  list<\App\Models\Person>  $items
     * @return array<Sided>
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
     * @param  array<int, list<\App\Models\Person>>  $groups  Grouped athletes by continent
     * @param  list<\App\Models\Person>  $items  Initial value
     * @return list<\App\Models\Person>
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
     * Split the items into upper and lower section.
     *
     * @param  list<\App\Models\Person>  $name
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
