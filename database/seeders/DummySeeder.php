<?php

namespace Database\Seeders;

use App\Enums\ParticipantType;
use App\Models\Classification;
use App\Models\Continent;
use App\Models\Participant;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = Classification::all();
        $continents = $this->generateContinents($classes);

        $this->generateTournaments($continents);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Classification>  $classes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function generateContinents($classes)
    {
        return Continent::factory(15)
            ->sequence(static fn (Sequence $sequence) => [
                'name' => 'Kontingen '.($sequence->index + 1),
            ])
            ->has(
                Participant::factory(2)->asPic(),
                'participants'
            )
            ->has(
                Participant::factory(20)
                    ->sequence(static fn () => [
                        'class_id' => fake()->randomElement($classes)->getKey(),
                    ])
                    ->asContestant(),
                'participants'
            )
            ->createMany();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Continent>  $continents
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function generateTournaments($continents)
    {
        $participants = $continents->reduce(
            function ($participants, $continent) {
                $contestants = $continent->participants()
                    ->where('type', ParticipantType::Contestant)
                    ->take(fake()->numberBetween(5, 20))
                    ->get();

                return $participants->merge($contestants);
            },
            collect()
        );

        return Tournament::factory(10)
            ->sequence(static function (Sequence $sequence) {
                $criteria = $sequence->index < 6;
                $fake = Carbon::parse(fake()->dateTimeThisMonth());
                $start = $criteria
                    ? $fake->subWeeks(6 - $sequence->index)
                    : $fake->addWeeks($sequence->index - 6);
                $created = $criteria ? $start : now()->addMinutes($sequence->index);

                return [
                    'title' => 'Turnamen '.($sequence->index + 1),
                    'description' => 'Contoh keterangan turnamen '.($sequence->index + 1),
                    'start_date' => $start,
                    'finish_date' => $sequence->index < 6
                        ? fake()->dateTimeBetween($start, $start->clone()->addWeek())
                        : null,
                    'created_at' => $created,
                    'updated_at' => $created,
                ];
            })
            ->hasAttached($participants, fn () => [
                // .
            ])
            ->createMany();
    }
}
