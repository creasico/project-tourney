<?php

namespace Database\Factories\Helpers;

use App\Enums\Gender;

trait WithGender
{
    private static ?Gender $gender;

    private function fakeGender()
    {
        return self::$gender ??= fake()->randomElement(Gender::cases());
    }

    public function withGender(Gender $gender): static
    {
        return $this->state([
            'gender' => self::$gender ??= $gender,
        ]);
    }
}
