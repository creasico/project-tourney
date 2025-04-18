<?php

namespace Database\Factories;

use App\Enums\TournamentLevel;
use App\Models\Classification;
use App\Models\Person;
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
            'published_at' => null,
        ];
    }

    public function withParticipants(?PersonFactory $participants = null, array $pivot = [])
    {
        return $this->hasAttached(
            $participants ?? Person::factory(),
            $pivot,
            'participants'
        );
    }

    public function withClassifications(?ClassificationFactory $classifications = null, array $pivot = [])
    {
        return $this->hasAttached(
            $classifications ?? Classification::factory(),
            $pivot,
            'classes'
        );
    }

    public function withLevel(TournamentLevel $level)
    {
        return $this->state(['level' => $level]);
    }
}
