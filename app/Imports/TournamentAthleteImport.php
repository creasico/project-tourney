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

final class TournamentAthleteImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public function __construct(
        private Tournament $tournament,
    ) {}

    /**
     * @param  Collection<int, array>  $collection
     *
     * @codeCoverageIgnore
     */
    public function collection(Collection $collection)
    {
        $athletes = DB::transaction(
            fn () => $collection->map(
                fn ($item) => $this->toAthlete(
                    category: $item['category'],
                    classification: $item['classification'],
                    continent: $item['continent'],
                    name: $item['name'],
                    gender: $item['gender'],
                    ageRange: $item['age_range'],
                )
            )
        );

        dispatch_sync(new AthletesParticipation($this->tournament, $athletes));
    }

    public function toAthlete(
        string $category,
        string $classification,
        string $continent,
        string $name,
        string $gender,
        string $ageRange,
    ): Person {
        $gender = Gender::fromLabel($gender);
        $category = Category::fromLabel($category);
        $ageRange = AgeRange::fromLabel($ageRange);

        $class = Classification::query()->firstOrCreate([
            'label' => $classification,
            'gender' => $gender,
            'category' => $category,
            'age_range' => $ageRange,
        ]);

        $continent = Continent::query()->firstOrCreate([
            'name' => $continent,
        ]);

        return Person::firstOrCreate([
            'name' => $name,
            'role' => ParticipantRole::Athlete,
            'continent_id' => $continent->getKey(),
            'class_id' => $class->getKey(),
            'gender' => $gender,
        ]);
    }
}
