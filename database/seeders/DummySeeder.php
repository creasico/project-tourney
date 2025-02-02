<?php

namespace Database\Seeders;

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
        $continents = $this->generateContinents();

        $this->generateTournaments($continents);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function generateContinents()
    {
        $ageRanges = Classification::onlyAges()->get();
        $weightRanges = Classification::onlyWeights()->get();

        return Continent::factory(15)
            ->sequence(static fn (Sequence $sequence) => [
                'name' => 'Kontingen '.($sequence->index + 1),
            ])
            ->has(
                Participant::factory(2)->asManager(),
                'members'
            )
            ->has(
                Participant::factory(20)
                    ->sequence(static fn () => [
                        'class_age_id' => fake()->randomElement($ageRanges)->getKey(),
                        'class_weight_id' => fake()->randomElement($weightRanges)->getKey(),
                    ])
                    ->asAthlete(),
                'members'
            )
            ->createMany();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Continent>  $continents
     * @return \Illuminate\Database\Eloquent\Collection
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
            })
            ->createMany();
    }
}
