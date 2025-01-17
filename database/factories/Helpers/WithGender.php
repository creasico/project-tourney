<?php

namespace Database\Factories\Helpers;

use App\Enums\Gender;

trait WithGender
{
    private function fakeGender()
    {
        return fake()->randomElement(Gender::cases());
    }

    public function withGender(Gender $gender)
    {
        return $this->state([
            'gender' => $gender,
        ]);
    }
}
