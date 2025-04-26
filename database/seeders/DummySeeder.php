<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Jobs\AthletesParticipation;
use App\Jobs\Matchmaking;
use App\Models\Classification;
use App\Models\Continent;
use App\Models\Matchup;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $continents = $this->generateContinents();

        $tournaments = $this->generateTournaments($continents);

        $this->generateMatches($tournaments);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Continent>
     */
    private function generateContinents()
    {
        $classes = Classification::all();

        $continents = Continent::factory(15)
            ->sequence(static fn (Sequence $sequence) => [
                'name' => 'Kontingen '.($sequence->index + 1),
            ])
            ->withManagers(2)
            ->createMany();

        return $continents->each(function ($continent) use ($classes) {
            foreach ($classes->groupBy('age_range') as $byAges) {
                if (fake()->boolean(30)) {
                    continue;
                }

                foreach ($byAges->groupBy('label') as $byLabels) {
                    if (fake()->boolean(30)) {
                        continue;
                    }

                    $class = fake()->randomElement($byLabels);
                    $count = fake()->numberBetween(2, 8);

                    Person::factory($count)
                        ->asAthlete()
                        ->state(fn (array $attrs) => [
                            'continent_id' => $continent,
                            'class_id' => $class,
                            'gender' => $class->gender,
                        ])
                        ->create();
                }
            }
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Continent>  $continents
     * @return \Illuminate\Database\Eloquent\Collection<int, Tournament>
     */
    private function generateTournaments($continents)
    {
        $continents = $continents->load('athletes');

        $tournaments = Tournament::factory(10)->sequence(static function (Sequence $sequence) {
            $criteria = $sequence->index < 5;
            $fake = Carbon::parse(fake()->dateTimeThisMonth());
            $start = $criteria
                ? $fake->subWeeks(6 - $sequence->index)
                : $fake->addWeeks($sequence->index - 6);
            $created = $criteria ? $start : now()->addMinutes($sequence->index);

            return [
                'title' => 'Turnamen '.($sequence->index + 1),
                'description' => 'Contoh keterangan turnamen '.($sequence->index + 1),
                'start_date' => $start,
                'finish_date' => $criteria
                    ? fake()->dateTimeBetween($start, $start->clone()->addWeek())
                    : null,
                'created_at' => $created,
                'updated_at' => $created,
                'published_at' => $sequence->index < 8 ? $start : null,
            ];
        })->createMany();

        $tournaments->each(static function (Tournament $tournament) use ($continents) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Person> */
            $participants = collect();

            foreach ($continents as $continent) {
                if (fake()->boolean(20)) {
                    continue;
                }

                foreach ($continent->athletes as $athlete) {
                    if (fake()->boolean(30)) {
                        continue;
                    }

                    $participants[] = $athlete;
                }
            }

            (new AthletesParticipation(
                tournament: $tournament,
                athletes: $athletes,
                shoudVerify: ! $tournament->is_draft
            ))->handle();

            DB::transaction(function () use ($tournament) {
                if ($tournament->is_draft) {
                    return;
                }

                $tournament->participants->each(fn ($person) => $tournament->verify($person));
            });

            dispatch(new Matchmaking($tournament));
        });

        return $tournaments;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Tournament>  $tournaments
     * @return \Illuminate\Database\Eloquent\Collection<int, Matchup>
     */
    private function generateMatches($tournaments)
    {
        $tournaments->each(function (Tournament $tournament) {
            //
        });

        return collect();
    }
}
