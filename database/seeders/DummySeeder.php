<?php

namespace Database\Seeders;

use App\Models\Classification;
use App\Models\Continent;
use App\Models\Matchup;
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

        return Continent::factory(15)
            ->sequence(static fn (Sequence $sequence) => [
                'name' => 'Kontingen '.($sequence->index + 1),
            ])
            ->withManagers(2)
            ->withAthletes(20, fn (array $attrs) => [
                'class_id' => $classes->where('gender', $attrs['gender'])->first(),
            ])
            ->createMany();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Continent>  $continents
     * @return \Illuminate\Database\Eloquent\Collection<int, Tournament>
     */
    private function generateTournaments($continents)
    {
        $athletes = $continents->reduce(
            function ($athletes, $continent) {
                $contestants = $continent->athletes()
                    ->take(fake()->numberBetween(5, 20))
                    ->get();

                return $athletes->merge($contestants);
            },
            collect()
        );

        return Tournament::factory(10)
            ->sequence(static function (Sequence $sequence) {
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
            })
            ->hasAttached($athletes, function (Tournament $model) {
                $disqualified = $model->is_started && fake()->boolean(10)
                    ? fake()->dateTimeBetween($model->start_date, $model->start_date->addDays(7))
                    : null;

                return [
                    // 'rank_number' => null,
                    'draw_number' => $model->is_finished ? fake()->numberBetween(1, 20) : 0,
                    // 'medal' => null,
                    'knocked_at' => $model->is_started && $disqualified === null && fake()->boolean(40)
                        ? fake()->dateTimeBetween($model->start_date, $model->start_date->addDays(7))
                        : null,
                    'disqualification_reason' => $disqualified !== null && fake()->boolean(40)
                        ? fake()->sentence()
                        : null,
                    'disqualified_at' => $disqualified,
                    'verified_at' => $model->is_started
                        ? fake()->dateTimeBetween($model->start_date->subDays(10), $model->start_date)
                        : null,
                ];
            }, relationship: 'participants')
            ->createMany();
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
