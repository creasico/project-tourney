<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Jobs\AthletesParticipation;
use App\Models\Classification;
use App\Models\Continent;
use App\Models\Person;
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
        if (app()->environment('production')) {
            return;
        }

        $continents = $this->generateContinentsAndAthletes();

        $tournaments = $this->generateTournaments($continents);

        $this->generateMatches($tournaments, $continents);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Continent>
     */
    private function generateContinentsAndAthletes()
    {
        $classes = Classification::all();

        $continents = Continent::factory(15)
            ->sequence(static fn (Sequence $sequence) => [
                'name' => 'Kontingen '.($sequence->index + 1),
            ])
            ->withManagers(2)
            ->createMany();

        return $continents->each(function ($continent) use ($classes) {
            $athletes = [];
            $now = now();

            foreach ($classes->groupBy('age_range') as $byAges) {
                if (fake()->boolean(25)) {
                    continue;
                }

                foreach ($byAges as $class) {
                    $count = fake()->numberBetween(0, 3);

                    if ($count === 0) {
                        continue;
                    }

                    /** @var \Illuminate\Support\Collection<int, Person> */
                    $participants = Person::factory($count)
                        ->asAthlete(withClassification: $class)
                        ->state(fn (array $attrs) => [
                            'continent_id' => $continent->id,
                            'gender' => $class->gender,
                        ])
                        ->make();

                    foreach ($participants as $athlete) {
                        $athlete->id = $athlete->newUniqueId();
                        $athlete->created_at = $now;
                        $athlete->updated_at = $now;

                        $athletes[] = $athlete->toArray();
                    }
                }
            }

            Person::insert($athletes);
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Continent>  $continents
     * @return \Illuminate\Database\Eloquent\Collection<int, Tournament>
     */
    private function generateTournaments($continents)
    {
        return Tournament::factory(10)->sequence(static function (Sequence $sequence) {
            $finished = $sequence->index < 5;
            $fake = Carbon::parse(fake()->dateTimeThisMonth());
            $started = $finished
                ? $fake->subWeeks(6 - $sequence->index)
                : $fake->addWeeks($sequence->index - 6);
            $created = $finished ? $started : now()->addMinutes($sequence->index);

            return [
                'title' => 'Turnamen '.($sequence->index + 1),
                'description' => 'Contoh keterangan turnamen '.($sequence->index + 1),
                'start_date' => $started,
                'finish_date' => $finished
                    ? fake()->dateTimeBetween($started, $started->clone()->addWeek())
                    : null,
                'created_at' => $created,
                'updated_at' => $created,
                'published_at' => $sequence->index < 8 ? $started : null,
            ];
        })->createMany();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Tournament>  $tournaments
     * @param  \Illuminate\Database\Eloquent\Collection<int, Continent>  $continents
     */
    private function generateMatches($tournaments, $continents)
    {
        $continents = $continents->fresh('athletes');

        $tournaments->each(static function (Tournament $tournament) use ($continents) {
            /** @var \Illuminate\Support\Collection<int, \App\Models\Person> */
            $athletes = collect();

            foreach ($continents as $continent) {
                if (fake()->boolean(20)) {
                    continue;
                }

                foreach ($continent->athletes as $athlete) {
                    if (fake()->boolean(30)) {
                        continue;
                    }

                    $athletes[] = $athlete;
                }
            }

            (new AthletesParticipation(
                tournament: $tournament,
                athletes: $athletes,
                shoudVerify: ! $tournament->is_draft
            ))->handle();
        });
    }
}
