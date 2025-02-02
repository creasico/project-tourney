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
            'class_age_id' => Classification::factory()->asAge(),
            'class_weight_id' => Classification::factory()->asWeight(),
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

    public function asAthlete($age = null, $weight = null)
    {
        return $this->withRole(ParticipantRole::Athlete)->state(fn () => array_filter([
            'class_age_id' => $age,
            'class_weight_id' => $weight,
        ]));
    }

    public function asManager()
    {
        return $this->state([
            'class_age_id' => null,
            'class_weight_id' => null,
        ])->withRole(ParticipantRole::Manager);
    }
}
