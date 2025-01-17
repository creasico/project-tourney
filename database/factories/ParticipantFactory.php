<?php

namespace Database\Factories;

use App\Enums\ParticipantRole;
use App\Models\Classification;
use App\Models\Continent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    use Helpers\WithGender;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'continent_id' => Continent::factory(),
            'class_id' => Classification::factory(),
            'gender' => $this->fakeGender(),
            'name' => fn (array $attr) => implode(' ', [
                fake()->firstName($attr['gender']),
                fake()->lastName($attr['gender']),
            ]),
            'role' => fake()->randomElement(ParticipantRole::cases()),
        ];
    }

    public function withRole(ParticipantRole $role)
    {
        return $this->state([
            'role' => $role,
        ]);
    }

    public function asAthlete()
    {
        return $this->withRole(ParticipantRole::Athlete);
    }

    public function asManager()
    {
        return $this->state([
            'class_id' => null,
        ])->withRole(ParticipantRole::Manager);
    }
}
