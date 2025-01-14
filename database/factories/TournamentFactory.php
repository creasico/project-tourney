<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tournament>
 */
class TournamentFactory extends Factory
{
    private static ?\DateTime $startDate = null;

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
            'attr' => null,
            'start_date' => self::$startDate = fake()->dateTimeThisMonth(),
            'finish_date' => fake()->dateTimeBetween(self::$startDate),
        ];
    }

    public function unstarted()
    {
        return $this->state([
            'start_date' => null,
            'finish_date' => null,
        ]);
    }

    public function unfinished()
    {
        return $this->state([
            'finish_date' => null,
        ]);
    }
}
