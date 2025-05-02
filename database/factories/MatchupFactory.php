<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MatchSide;
use App\Models\Division;
use App\Models\Person;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Matchup>
 */
class MatchupFactory extends Factory
{
    private static ?DateTime $startedAt = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'next_side' => fake()->randomElement(MatchSide::cases()),
            'party_number' => fake()->numberBetween(0, 10),
            'round_number' => fake()->numberBetween(0, 10),
            'order' => fake()->numberBetween(0, 10),
            'is_bye' => fake()->boolean(),
            'attr' => null,
            'started_at' => self::$startedAt = fake()->dateTimeThisMonth(),
            'finished_at' => fake()->dateTimeBetween(self::$startedAt),
        ];
    }

    public function unstarted(): static
    {
        return $this->state([
            'started_at' => null,
            'finished_at' => null,
        ]);
    }

    public function unfinished(): static
    {
        return $this->state([
            'finished_at' => null,
        ]);
    }

    public function withAthletes(
        \Closure|int|null $count = null,
        ?PersonFactory $athletes = null,
        array $pivot = []
    ): static {
        return $this->hasAttached(
            $athletes ?? Person::factory(count: value($count))->asAthlete(),
            $pivot,
            'athletes'
        );
    }

    public function withDivision(
        \Closure|int|null $count = null,
        DivisionFactory|Division|null $divisions = null,
    ): static {
        return $this->for(
            $divisions ?? Division::factory(count: value($count)),
            'division',
        );
    }
}
