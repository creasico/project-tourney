<?php

namespace Database\Seeders;

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Models\Classification;
use Illuminate\Database\Seeder;

class ClassificationSeeder extends Seeder
{
    private $classes = [
        AgeRange::Early->value => [
            'A' => '26-28',
            'B' => '28-30',
            'C' => '30-32',
            'D' => '32-34',
            'E' => '34-36',
            'F' => '36-38',
            'G' => '38-40',
            'H' => '40-42',
            'I' => '42-44',
            'J' => '44-46',
            'K' => '46-48',
            'L' => '48-50',
            'M' => '51-52',
            'N' => '52-54',
            'O' => '54-56',
            'P' => '56-58',
            'Bebas' => '56-60',
        ],
        AgeRange::PreJunior->value => [
            'A' => '30-33',
            'B' => '33-36',
            'C' => '36-39',
            'D' => '39-42',
            'E' => '42-45',
            'F' => '45-48',
            'G' => '48-51',
            'H' => '51-54',
            'I' => '54-57',
            'J' => '57-60',
            'K' => '60-63',
            'L' => '63-66',
            'M' => '66-69',
            'N' => '69-72',
            'Bebas' => '72-75',
        ],
        AgeRange::Junior->value => [
            'A' => '39-43',
            'B' => '43-47',
            'C' => '47-51',
            'D' => '51-55',
            'E' => '55-59',
            'F' => '59-63',
            'G' => '63-67',
            'H' => '67-71',
            'I' => '71-75',
            'J' => '75-79',
            'K' => ['79-83', null],
            'L' => ['83-87', null],
            'Bebas' => ['87-99', '79-91'],
        ],
        AgeRange::Senior->value => [
            'A' => '45-50',
            'B' => '50-55',
            'C' => '55-60',
            'D' => '60-65',
            'E' => '65-70',
            'F' => '70-75',
            'G' => ['75-80', null],
            'H' => ['80-85', null],
            'I' => ['85-90', null],
            'J' => ['90-95', null],
            'Bebas' => ['>85', '>65'],
        ],
        AgeRange::MasterI->value => AgeRange::Senior,
        AgeRange::MasterII->value => AgeRange::Senior,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Classification::count() > 0) {
            return;
        }

        $classes = collect($this->classes)
            ->map(function (array|AgeRange $values) {
                if ($values instanceof AgeRange) {
                    $values = $this->classes[$values->value];
                }

                foreach ($values as $label => $ages) {
                    if (is_array($ages)) {
                        continue;
                    }

                    $values[$label] = [$ages, $ages];
                }

                return $values;
            })
            ->reduce(function (array $result, array $values, int $key) {
                $age = AgeRange::from($key);

                foreach ($values as $label => $weights) {
                    foreach ($weights as $gender => $weight) {
                        if ($weight === null) {
                            continue;
                        }

                        $result[] = [
                            'order' => count($result) + 1,
                            'label' => $label,
                            'age_range' => $age,
                            'weight_range' => $weight,
                            'gender' => match ($gender) {
                                0 => Gender::Male,
                                1 => Gender::Female,
                            },
                        ];
                    }
                }

                return $result;
            }, []);

        foreach ($classes as $class) {
            Classification::create($class);
        }
    }
}
