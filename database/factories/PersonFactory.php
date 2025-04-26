<?php

namespace Database\Factories;

use App\Enums\AgeRange;
use App\Enums\ParticipantRole;
use App\Models\Classification;
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
            'gender' => $this->fakeGender(),
            'name' => fn (array $attr) => implode(' ', [
                fake()->firstName($attr['gender'] ?? self::$gender),
                fake()->lastName($attr['gender'] ?? self::$gender),
            ]),
            'role' => fake()->randomElement(ParticipantRole::cases()),
        ];
    }

    public function withUser()
    {
        return $this->for(
            User::factory(),
            'credential'
        );
    }

    public function withRole(ParticipantRole $role)
    {
        return $this->state([
            'role' => $role,
        ]);
    }

    public function asAthlete(?AgeRange $age = null, ?string $weight = null, bool $createClass = true)
    {
        $state = $this->withRole(ParticipantRole::Athlete);

        if (! $createClass) {
            return $state;
        }

        return $state->for(
            Classification::factory()->withGender(self::$gender)->withRange($age, $weight),
        );
    }

    public function asManager()
    {
        return $this->withRole(ParticipantRole::Manager);
    }
}
