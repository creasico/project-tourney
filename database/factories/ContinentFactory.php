<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Continent>
 */
class ContinentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => null,
            'name' => fake()->words(asText: true),
            'attr' => null,
        ];
    }

    /**
     * @param  \Closure(array, \App\Models\Continent)|array  $state
     */
    public function withManagers(?int $count = null, \Closure|array $state = [])
    {
        return $this->has(
            Person::factory($count)->asManager()->state($state),
            'managers'
        );
    }

    /**
     * @param  \Closure(array, \App\Models\Continent)|array  $state
     */
    public function withAthletes(?int $count = null, \Closure|array $state = [])
    {
        return $this->has(
            Person::factory($count)->asAthlete()->state($state),
            'athletes'
        );
    }
}
