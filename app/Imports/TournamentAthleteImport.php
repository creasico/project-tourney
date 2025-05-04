<?php

namespace App\Imports;

use App\Enums\AgeRange;
use App\Enums\Category;
use App\Enums\Gender;
use App\Enums\ParticipantRole;
use App\Jobs\AthletesParticipation;
use App\Models\Classification;
use App\Models\Continent;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TournamentAthleteImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public function __construct(
        private Tournament $tournament,
    ) {}

    public function collection(Collection $collection)
    {
        DB::transaction(function () use ($collection) {
            $athletes = collect();

            foreach ($collection as $item) {
                $gender = Gender::fromLabel($item['gender']);
                $category = Category::fromLabel($item['category']);
                $ageRange = AgeRange::fromLabel($item['age_range']);

                /** @var Classification */
                $class = Classification::query()->firstOrCreate([
                    'label' => $item['classification'],
                    'gender' => $gender,
                    'category' => $category,
                    'age_range' => $ageRange,
                ]);

                /** @var Continent */
                $continent = Continent::query()->firstOrCreate([
                    'name' => $item['continent'],
                ]);

                $athletes[] = Person::firstOrCreate([
                    'name' => $item['name'],
                    'role' => ParticipantRole::Athlete,
                    'continent_id' => $continent->getKey(),
                    'class_id' => $class->getKey(),
                    'gender' => $gender,
                ]);
            }

            (new AthletesParticipation($this->tournament, $athletes))->handle();
        });
    }
}
