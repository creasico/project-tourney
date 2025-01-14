<?php

namespace Database\Factories;

use App\Enums\TournamentLevel;
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
            'finish_date' => fn (array $attr) => fake()->dateTimeBetween($attr['start_date']),
        ];
    }

    public function withLevel(TournamentLevel $level)
    {
        return $this->state(['level' => $level]);
    }
}
