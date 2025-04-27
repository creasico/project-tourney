<?php

namespace Database\Factories;

use App\Enums\MatchBye;
use App\Enums\TournamentLevel;
use App\Models\Classification;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tournament>
 */
class TournamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(asText: true),
            'description' => null,
            'level' => fake()->randomElement(TournamentLevel::cases())->value,
            'attr' => null,
            'start_date' => fake()->dateTimeThisMonth(),
            'finish_date' => fn (array $attr) => $attr['start_date']
                ? fake()->dateTimeBetween($attr['start_date'], '1 week')
                : null,
            'published_at' => null,
        ];
    }

    public function published(bool $started = true)
    {
        return $this->state([
            'published_at' => $started ? now()->subDay() : now(),
            'start_date' => $started ? now()->subDay() : now()->addDay(),
        ]);
    }

    public function finished()
    {
        return $this->state([
            'published_at' => now()->subWeek(),
            'start_date' => now()->subDays(3),
            'finish_date' => now()->subDay(),
        ]);
    }

    public function withParticipants(
        PersonFactory|Person|null $participants = null,
        array $pivot = []
    ): static {
        return $this->hasAttached(
            $participants ?? Person::factory()->asAthlete(),
            $pivot,
            'participants'
        )->afterCreating(function ($tournament) {
            foreach ($tournament->participants->pluck('class_id') as $ids) {
                $tournament->classes()->attach($ids);
            }
        });
    }

    public function withClassifications(
        ClassificationFactory|Classification|null $classifications = null,
        int $division = 0,
        ?MatchBye $bye = null,
    ): static {
        return $this->hasAttached(
            $classifications ?? Classification::factory(),
            array_filter([
                'division' => $division,
                'bye' => $bye,
            ]),
            'classes'
        );
    }
}
