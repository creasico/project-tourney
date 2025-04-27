<?php

namespace App\Listeners;

use App\Enums\MatchSide;
use App\Events\AthletesParticipated;
use App\Events\MatchupInitialized;
use App\Models\Classification;
use App\Models\Division;
use App\Models\Tournament;
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
            $athletes = $this->randomizeAthletes($event->class->athletes->shuffle());

            $athletesCount = $athletes->count();
            $division = $event->group->division > 2 ? $event->group->division : $athletesCount;

            foreach ($athletes->chunk($division) as $i => $chunks) {
                $label = $event->class->display;

                if ($division !== $athletesCount) {
                    $i++;
                    $label .= " {$i}";
                }

                $division = $event->group->divisions()->create([
                    'label' => $label,
                ]);

                $this->createMatchups($chunks, $tournament, $event->class, $division);
            }

            event(new MatchupInitialized($tournament, $event->class->id));
        });
    }

    /**
     * @param  Collection<int, \App\Models\Person>  $athletes
     */
    private function createMatchups(
        Collection $athletes,
        Tournament $tournament,
        Classification $class,
        Division $division,
    ): void {
        $sides = MatchSide::cases();

        foreach ($athletes->chunk(2) as $parties) {
            /** @var \App\Models\Matchup */
            $match = $tournament->matches()->create([
                'division_id' => $division->id,
                'class_id' => $class->id,
                'is_bye' => $parties->count() === 1,
            ]);

            foreach ($parties->values() as $a => $athlete) {
                $match->attachAthlete($athlete, $sides[$a]);

                $tournament->participants()->updateExistingPivot($athlete, [
                    'match_id' => $match->id,
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, \App\Models\Person>  $athletes
     * @return Collection<int, \App\Models\Person>
     */
    private function randomizeAthletes(Collection $athletes)
    {
        $randomized = [];
        $continents = $athletes->pluck('continent_id', 'id')->all();
        $count = count($continents);
        $result = collect();

        while (count($randomized) < $count) {
            $last = end($randomized);
            $id = array_key_first($continents);

            if ($last && $last === $continents[$id]) {
                $others = array_filter($continents, fn ($val) => $last !== $val);

                if (! empty($others)) {
                    $id = array_key_first($others);
                }
            }

            $randomized[$id] = $continents[$id];
            $result[] = $athletes->first(fn ($a) => $a->id === $id);

            unset($continents[$id]);
        }

        return $result;
    }
}
