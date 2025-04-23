<?php

namespace Database\Factories;

use App\Models\Matchup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MatchRevision>
 */
class MatchRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'match_id' => Matchup::factory(),
            'reason' => fake()->words(asText: true),
        ];
    }
}
