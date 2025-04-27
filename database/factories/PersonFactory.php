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

    public function withContinent()
    {
        return $this->for(
            Continent::factory(),
            'continent'
        );
    }

    public function withRole(ParticipantRole $role)
    {
        return $this->state([
            'role' => $role,
        ]);
    }

    public function asAthlete(
        ClassificationFactory|Classification|false|null $withClassification = null,
        ?AgeRange $age = null,
        ?string $weight = null,
    ): static {
        $state = $this->withRole(ParticipantRole::Athlete);

        if ($withClassification === false) {
            return $state;
        }

        if ($withClassification instanceof Classification) {
            $state = $state->withGender($withClassification->gender);
        }

        return $state->for(
            $withClassification ?? Classification::factory()
                ->withRange($age, $weight)
                ->state(fn (array $attr, $rel) => array_filter([
                    'gender' => $rel?->gender,
                ])),
        );
    }

    public function asManager()
    {
        return $this->withRole(ParticipantRole::Manager);
    }
}
