<?php

namespace Database\Factories;

use App\Enums\ClassificationTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classification>
 */
class ClassificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->words(asText: true),
            'term' => fake()->randomElement(ClassificationTerm::toArray()),
            'description' => null,
            'order' => null,
        ];
    }

    public function withTerm(ClassificationTerm $term)
    {
        return $this->state(fn () => [
            'term' => $term,
        ]);
    }

    public function asAge()
    {
        return $this->withTerm(ClassificationTerm::Age);
    }

    public function asWeight()
    {
        return $this->withTerm(ClassificationTerm::Weight);
    }
}
