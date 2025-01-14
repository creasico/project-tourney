<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\ParticipantType;
use App\Models\Classification;
use App\Models\Continent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    private static ?Gender $gender = null;

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
            'gender' => self::$gender = fake()->randomElement(Gender::cases()),
            'name' => implode(' ', [
                fake()->firstName(self::$gender->value),
                fake()->lastName(self::$gender->value),
            ]),
            'type' => fake()->randomElement(ParticipantType::cases()),
        ];
    }

    public function withGender(Gender $gender)
    {
        return $this->state([
            'gender' => self::$gender = $gender,
        ]);
    }

    public function withType(ParticipantType $type)
    {
        return $this->state([
            'type' => $type,
        ]);
    }

    public function asContestant()
    {
        return $this->withType(ParticipantType::Contestant);
    }

    public function asPic()
    {
        return $this->state([
            'class_id' => null,
        ])->withType(ParticipantType::PIC);
    }
}
