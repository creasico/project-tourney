<?php

namespace Database\Factories;

use App\Enums\AgeRange;
use App\Enums\Category;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classification>
 */
class ClassificationFactory extends Factory
{
    /** @use Helpers\WithAthletes<\App\Models\Classification> */
    use Helpers\WithAthletes, Helpers\WithGender;

    private array $ranges = [
        'A' => '26-28',
        'B' => '28-30',
        'C' => '30-32',
        'D' => '32-34',
        'E' => '34-36',
        'Bebas' => '56-60',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => $label = fake()->randomElement(array_keys($this->ranges)),
            'description' => null,
            'gender' => $this->fakeGender(),
            'category' => Category::Match,
            'age_range' => fake()->randomElement(AgeRange::cases()),
            'weight_range' => fn () => $this->ranges[$label],
            'order' => null,
        ];
    }

    public function withRange(?AgeRange $age = null, ?string $weight = null): static
    {
        return $this->state(fn (array $attrs) => [
            'age_range' => $age ?? fake()->randomElement(AgeRange::cases()),
            'weight_range' => $weight ?? $this->ranges[$attrs['label']],
        ]);
    }

    public function withTournaments(
        ?TournamentFactory $tournaments = null,
        array $pivot = []
    ): static {
        return $this->hasAttached(
            $tournaments ?? Tournament::factory(),
            $pivot,
            'tournaments',
        );
    }
}
