<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PrizePool>
 */
class PriePoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rank_number' => fake()->numberBetween(0, 10),
            'label' => fake()->words(asText: true),
            'description' => null,
        ];
    }
}
