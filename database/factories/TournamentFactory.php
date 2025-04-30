<?php

namespace Database\Factories;

use App\Enums\MatchBye;
use App\Enums\TournamentLevel;
use App\Models\Classification;
use App\Models\Continent;
use App\Models\Matchup;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tournament>
 */
class TournamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(asText: true),
            'description' => null,
            'level' => fake()->randomElement(TournamentLevel::cases())->value,
            'attr' => null,
            'start_date' => fake()->dateTimeThisMonth(),
            'finish_date' => fn (array $attr) => $attr['start_date']
                ? fake()->dateTimeBetween($attr['start_date'], '1 week')
                : null,
            'published_at' => null,
        ];
    }

    public function published(bool $started = true)
    {
        return $this->state([
            'published_at' => $started ? now()->subDay() : now(),
            'start_date' => $started ? now()->subDay() : now()->addDay(),
        ]);
    }

    public function finished()
    {
        return $this->state([
            'published_at' => now()->subWeek(),
            'start_date' => now()->subDays(3),
            'finish_date' => now()->subDay(),
        ]);
    }

    public function withAthletes(
        \Closure|int|null $count = null,
        PersonFactory|Person|null $participants = null,
        ClassificationFactory|Classification|false|null $withClassification = null,
        \Closure|ContinentFactory|Continent|null $withContinent = null,
        array $pivot = [],
    ): static {
        if ($count instanceof \Closure) {
            $count = $count();
        }

        return $this->hasAttached(
            $participants ?? Person::factory($count)
                ->asAthlete($withClassification)
                ->state(function (array $attr, Tournament $tournament) use ($withContinent) {
                    if ($class = $tournament->classes->first()) {
                        $attr['class_id'] = $class->id;
                        $attr['gender'] = $class->gender;
                    }

                    if ($withContinent instanceof \Closure) {
                        $withContinent = $withContinent();
                    }

                    $attr['continent_id'] = $withContinent ?? Continent::factory();

                    return $attr;
                }),
            $pivot,
            'participants'
        );
    }

    public function withClassifications(
        ClassificationFactory|Classification|null $classifications = null,
        int $division = 0,
        ?MatchBye $bye = null,
        \Closure|int|null $count = null,
    ): static {
        if ($count instanceof \Closure) {
            $count = $count();
        }

        return $this->hasAttached(
            $classifications ?? Classification::factory($count),
            array_filter([
                'division' => $division,
                'bye' => $bye,
            ]),
            'classes'
        );
    }

    public function withMatches(
        MatchupFactory|Matchup|null $matches = null,
        \Closure|int|null $count = null,
    ): static {
        if ($count instanceof \Closure) {
            $count = $count();
        }

        return $this->has(
            $matches ?? Matchup::factory($count)->withDivision(),
            'matches',
        );
    }
}
