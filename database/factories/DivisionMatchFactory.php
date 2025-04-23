<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DivisionMatch>
 */
class DivisionMatchFactory extends Factory
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
            'label' => fake()->sentence(),
            'gender' => $this->fakeGender(),
            'attr' => null,
        ];
    }
}
