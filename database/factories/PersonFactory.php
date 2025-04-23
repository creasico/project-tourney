<?php

namespace Database\Factories;

use App\Enums\AgeRange;
use App\Enums\ParticipantRole;
use App\Models\Classification;
use App\Models\Continent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
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
            'gender' => $this->fakeGender(),
            'name' => fn (array $attr) => implode(' ', [
                fake()->firstName($attr['gender']),
                fake()->lastName($attr['gender']),
            ]),
            'role' => fake()->randomElement(ParticipantRole::cases()),
        ];
    }

    public function withUser()
    {
        return $this->state([
            'user_id' => User::factory(),
        ]);
    }

    public function withRole(ParticipantRole $role)
    {
        return $this->state([
            'role' => $role,
        ]);
    }

    public function withClassification(?AgeRange $age = null, ?string $weight = null)
    {
        return $this->for(
            Classification::factory()->withRange($age, $weight),
        );
    }

    public function asAthlete(?Classification $class = null)
    {
        return $this->withRole(ParticipantRole::Athlete)->state([
            'class_id' => $class?->id,
        ]);
    }

    public function asManager()
    {
        return $this->withRole(ParticipantRole::Manager);
    }
}
