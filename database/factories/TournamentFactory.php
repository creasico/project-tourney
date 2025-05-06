<?php

namespace Database\Factories;

use App\Enums\MatchBye;
use App\Enums\MatchSide;
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

    public function unstarted(): static
    {
        return $this->state([
            'start_date' => null,
            'finish_date' => null,
        ]);
    }

    public function unfinished(): static
    {
        return $this->state([
            'finish_date' => null,
        ]);
    }

    public function published(bool $started = true): static
    {
        return $this->state([
            'published_at' => $started ? now()->subDay() : now(),
            'start_date' => $started ? now()->subDay() : now()->addDay(),
        ]);
    }

    public function finished(): static
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
        return $this->hasAttached(
            $participants ?? Person::factory(count: value($count))
                ->asAthlete(withClassification: $withClassification)
                ->state(function (array $attr, Tournament $tournament) use ($withContinent) {
                    if ($class = $tournament->classes->first()) {
                        $attr['class_id'] = $class->id;
                        $attr['gender'] = $class->gender;
                    }

                    $attr['continent_id'] = value($withContinent) ?? Continent::factory();

                    return $attr;
                }),
            $pivot,
            'participants'
        );
    }

    public function withClassifications(
        \Closure|int|null $count = null,
        ClassificationFactory|Classification|null $classifications = null,
        int $division = 0,
        ?MatchBye $bye = null,
    ): static {
        return $this->hasAttached(
            $classifications ?? Classification::factory(count: value($count)),
            array_filter([
                'division' => $division,
                'bye' => $bye,
            ]),
            'classes'
        );
    }

    public function withMatches(
        \Closure|int|null $count = null,
        MatchupFactory|Matchup|null $matches = null,
        \Closure|array $state = [],
    ): static {
        return $this->has(
            $matches ?? Matchup::factory(count: value($count))
                ->withAthletes(side: MatchSide::Blue, state: fn ($attr, Matchup $match) => [
                    ...$attr,
                    'class_id' => $match->class_id ?? $attr['class_id'],
                    'gender' => $match->class?->gender ?? $attr['gender'],
                ])
                ->withAthletes(side: MatchSide::Red, state: fn ($attr, Matchup $match) => [
                    ...$attr,
                    'class_id' => $match->class_id ?? $attr['class_id'],
                    'gender' => $match->class?->gender ?? $attr['gender'],
                ])
                ->withDivision()
                ->afterCreating(function (Matchup $match, Tournament $tournament) {
                    foreach ($match->athletes as $athlete) {
                        $tournament->participants()->attach($athlete, [
                            'match_id' => $match->getKey(),
                        ]);
                    }
                })
                ->state($state),
            'matches',
        );
    }
}
