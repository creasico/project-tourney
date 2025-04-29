<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AthletesParticipated;
use App\Events\MatchupInitialized;
use App\Exceptions\UnprocessableMatchupException;
use App\Support\Sided;
use App\Support\Sliced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class InitializeMatchups implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AthletesParticipated $event): void
    {
        DB::transaction(function () use ($event) {
            $tournament = $event->tournament->fresh();
            $class = $tournament->withClassifiedAthletes()
                ->where('class_id', $event->classId)
                ->first();

            $athletes = $this->prepareAthletes($class->athletes);
            $athletesCount = $class->athletes->count();
            $division = $class->group->division > 2 ? $class->group->division : $athletesCount;

            foreach (array_chunk($athletes, $division) as $i => $participants) {
                $label = $class->display;

                if ($division !== $athletesCount) {
                    $i++;
                    $label .= " {$i}";
                }

                $division = $class->group->divisions()->create([
                    'label' => $label,
                    'tournament_id' => $tournament->id,
                ]);

                $participants = $this->determineSide(array_map(
                    fn ($row) => $class->athletes->where('id', $row['id'])->first(),
                    $participants,
                ));

                foreach ($participants as $p => $parties) {
                    $p++;

                    /** @var \App\Models\Matchup */
                    $match = $tournament->matches()->create([
                        'division_id' => $division->id,
                        'class_id' => $class->id,
                        'is_bye' => $parties->isBye(),
                        'party_number' => $p,
                        'attr' => [
                            'index' => $p,
                        ],
                    ]);

                    $match->addAthletes($parties, $tournament);
                }

                event(new MatchupInitialized($tournament, $class->id, $division->id));
            }
        });
    }

    /**
     * @param  Collection<int, \App\Models\Person>  $athletes
     * @return array<int, array>
     */
    public function prepareAthletes(Collection $athletes): array
    {
        $groupedAthletes = $athletes->groupBy('continent_id')
            ->each->pluck('id')
            ->values()
            ->toArray();

        if (count($groupedAthletes) === 1) {
            throw UnprocessableMatchupException::singleContinent();
        }

        $result = $this->suffle($groupedAthletes);
        $count = count($result);

        // At this stage we might still find some athletes facing their comrade
        // in the matchup, now we need to ensure that they will be resuffled.
        foreach ($result as $i => $row) {
            if ($i === 0) {
                continue;
            }

            if ($row['continent_id'] !== $result[$i - 1]['continent_id']) {
                continue;
            }

            // Let's traverse the result backward and find an opponent for the
            // athlets from another continent by checking on each iteration
            // doesn't come from the same continent.
            for ($r = $count - 1; $r >= 0; $r--) {
                $range = array_filter([
                    $result[$r]['continent_id'], // current
                    $result[$r - 1]['continent_id'], // previous
                    $result[$r + 1]['continent_id'] ?? null, // next, if any
                ]);

                if (
                    in_array($r, [$i, $i - 1]) ||
                    in_array($row['continent_id'], $range)
                ) {
                    continue;
                }

                $result[$i] = $result[$r];
                $result[$r] = $row;

                break;
            }
        }

        return $result;
    }

    /**
     * @param  list<\App\Models\Person>  $athletes
     * @return list<Sided>
     */
    public function determineSide(array $athletes): array
    {
        $half = count($athletes);
        $slices = [];

        while ($half > 0) {
            $half = (int) floor($half / 2);

            if (count($slices) === 0) {
                $slices[] = $this->slice($athletes, $half);

                continue;
            }

            $parts = [];

            foreach ($slices as $slice) {
                if (count($slice->upper) === 1 && count($slice->lower) === 1) {
                    $parts[] = $slice;

                    continue;
                }

                // On last chunk iteration that has 2 upper and 1 lower
                // Swap their participant to the correct allocation
                if (count($slice->upper) === 2 && count($slice->lower) === 1) {
                    array_unshift($slice->lower, array_pop($slice->upper));
                }

                foreach ($slice as $side) {
                    if (count($side) > 1) {
                        $parts[] = $this->slice($side, $half);

                        continue;
                    }

                    $parts[] = new Sliced($side);
                }
            }

            $slices = [];
            array_push($slices, ...$parts);
        }

        $result = [];

        foreach ($slices as $slice) {
            if (empty($slice->upper) && count($slice->lower) === 1) {
                $slice->upper[] = array_pop($slice->lower);
            }

            $result[] = new Sided(
                $slice->upper[0],
                $slice->lower[0] ?? null
            );
        }

        return $result;
    }

    /**
     * @return Sliced
     */
    private function slice(array $items, int $slice)
    {
        if (floor(count($items) / 2) > $slice) {
            $slice++;
        }

        return new Sliced(
            upper: array_slice($items, 0, $slice),
            lower: array_slice($items, $slice),
        );
    }

    /**
     * Recursive method to find opponents for each athletes from another continents
     *
     * @param  array<int, array<int, array>>  $groups  Grouped athletes by continent
     * @param  array<int, array>  $items  Initial value
     */
    private function suffle(array $groups, array &$items = []): array
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
            krsort($others);
            $opponents = [];

            foreach (array_keys($others) as $k) {
                $opponents[] = array_splice($items, $k, 1)[0];
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
            return $this->suffle($groups, $items);
        }

        return $items;
    }
}
