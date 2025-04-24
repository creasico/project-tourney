<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\MatchSide;
use App\Models\Classification;
use App\Models\MatchGroup;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class ProceedMatchmaking implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * @param  Collection<int, \App\Models\Person>  $athletes
     */
    public function __construct(
        protected Tournament $tournament,
        protected Collection $athletes,
        protected MatchGroup $group,
        protected Classification $class,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $athletes = $this->randomizeAthletes();

        $shouldChunked = $this->group->division > 2;
        $chunk = $shouldChunked
            ? $this->group->division
            : $athletes->count();

        $chunked = $shouldChunked && $chunk !== $athletes->count();
        $label = $this->class->display;

        foreach ($athletes->chunk($chunk) as $i => $chunks) {
            if ($chunked) {
                $i++;
                $label .= " {$i}";
            }

            $division = $this->group->divisions()->create([
                'label' => $label,
            ]);

            dispatch(new GenerateMatches($chunks, $this->tournament, $this->class->id, $division->id));
        }
    }

    /**
     * @return Collection<int, \App\Models\Person>
     */
    public function randomizeAthletes()
    {
        $athletes = $this->athletes->shuffle();
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

    public function createMatchParty(Person $blue, Person $red)
    {
        /** @var \App\Models\Matchup */
        $match = $this->tournament->matches()->create([
            'group_id' => $this->group->id,
            'class_id' => $this->class->id,
        ]);

        $match->attachAthlete($blue, MatchSide::Blue);
        $match->attachAthlete($red, MatchSide::Red);
    }
}
