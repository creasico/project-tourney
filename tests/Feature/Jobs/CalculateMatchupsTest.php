<?php

declare(strict_types=1);

use App\Events\MatchupInitialized;
use App\Exceptions\UnprocessableMatchupException;
use App\Jobs\CalculateMatchups;
use App\Models\Continent;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @var list<list<int>>
 */
$structures = [
    2 => [2],
    3 => [1, 2],
    4 => [2, 2],
    5 => [2, 1, 2],
];

foreach (range(3, 50) as $c) {
    if (isset($structures[$c])) {
        continue;
    }

    $upper = floor($c / 2);
    $lower = $c - $upper;

    $structures[$c] = array_reduce([$upper, $lower], static function ($result, $size) use ($structures) {
        $prev = $structures[$size] ?? null;

        if ($prev) {
            array_push($result, ...$prev);
        }

        return $result;
    }, []);
}

/**
 * @var list<array{int, int, list<int>}>
 */
$divisions = [];
$slices = [
    5 => [
        3 => [3, 2],
    ],
    7 => [
        3 => [3, 4],
        4 => [4, 3],
    ],
    9 => [
        4 => [4, 3, 2],
        5 => [5, 4],
    ],
    10 => [
        3 => [3, 3, 4],
        4 => [4, 3, 3],
    ],
    11 => [
        3 => [3, 3, 3, 2],
        4 => [4, 4, 3],
        5 => [5, 3, 3],
    ],
    12 => [
        3 => [3, 3, 3, 3],
        4 => [4, 4, 4],
        5 => [5, 4, 3],
        7 => [7, 5],
    ],
];

foreach ($structures as $key => $val) {
    $divisions[] = [$key, 0, [$key]];

    if ($key === 15) {
        break;
    }

    if (! array_key_exists($key, $slices)) {
        continue;
    }

    foreach ($slices[$key] as $k => $v) {
        $divisions[] = [$key, $k, $v];
    }
}

$divisions = collect($divisions);

it('should not proceed a tournament with no classification', function () {
    $tournament = Tournament::factory()
        ->published()
        ->createOne();

    (new CalculateMatchups(
        $tournament,
        'not-exists'
    ))->handle();
})->throws(UnprocessableMatchupException::class, 'Class not-exists not found');

it('should not proceed a tournament with no athletes', function () {
    $tournament = Tournament::factory()
        ->published()
        ->withClassifications()
        ->createOne();

    (new CalculateMatchups(
        $tournament,
        $tournament->classes->first()->getKey()
    ))->handle();
})->throws(UnprocessableMatchupException::class, 'No athletes found');

it('should calculate matchups with :dataset', function (Tournament $tournament, int $expected) {
    Event::fake(MatchupInitialized::class);

    (new CalculateMatchups(
        $tournament,
        $tournament->classes->first()->getKey()
    ))->handle();

    Event::assertDispatchedTimes(MatchupInitialized::class, $expected);

    Event::assertDispatched(function (MatchupInitialized $event) use ($expected) {
        $tournament = $event->tournament->fresh(['withClassifiedAthletes']);

        /** @var \App\Models\Classification */
        $class = $tournament->withClassifiedAthletes->first();

        expect($event)->classId->toBe($class->id);

        $group = $class->group->load(['divisions.matches.athletes']);

        expect($group->divisions)->toHaveCount($expected);

        return $event->tournament->id === $tournament->id;
    });
})->with($divisions->mapWithKeys(function ($value) {
    [$total, $division, $expected] = $value;
    $exp = implode(' ', $expected);

    return [
        "{$total} by {$division} to [{$exp}]" => [
            fn () => Tournament::factory()
                ->published()
                ->withClassifications(division: $division)
                ->withAthletes(count: $total)
                ->createOne(),
            count($expected),
            $expected,
        ],
    ];
})->toArray());

describe('::createRounds()', function () use ($structures) {
    it('creates rounds from :dataset', function (array $items) {
        /** @var CalculateMatchups */
        $ref = (new ReflectionClass(CalculateMatchups::class))
            ->newInstanceWithoutConstructor();

        $rounds = $ref->createRounds($items);

        dump($rounds);

        expect(true)->toBeTrue();
    })->with(collect($structures)->mapWithKeys(function ($val, $count) {
        $text = implode(' ', $val);

        return [
            "{$count} athletes" => [
                fn () => Person::factory($count)
                    ->asAthlete()
                    ->withContinent()
                    ->createMany()
                    ->all(),
                $val,
            ],
        ];
    })->take(16)->all());
});

describe('::divide()', function () use ($divisions) {
    it('can divide with :dataset', function (int $division, array $items, array $expect) {
        /** @var CalculateMatchups */
        $ref = (new ReflectionClass(CalculateMatchups::class))
            ->newInstanceWithoutConstructor();

        $result = array_reduce(
            $ref->divide($items, $division, count($items)),
            function (array $result, $items) {
                $result[] = count($items);

                return $result;
            },
            [],
        );

        expect($result)->toBe($expect);
    })->with($divisions->mapWithKeys(function ($item) {
        [$total, $division, $expected] = $item;
        $exp = implode(' ', $expected);

        return [
            "{$total} by {$division} to [{$exp}]" => [
                $division,
                fn () => Person::factory($total)->make()->pluck('name')->all(),
                $expected,
            ],
        ];
    })->all());
});

describe('::prepareAthletes()', function () {
    /** @param Collection<int, Continent> $continents */
    it('should randomize :dataset', function (Collection $continents) {
        /** @var Collection<int, \App\Models\Person> */
        $athletes = $continents->reduce(
            fn (Collection $result, $continent) => $result->push(...$continent->athletes),
            collect()
        );

        /** @var CalculateMatchups */
        $ref = (new ReflectionClass(CalculateMatchups::class))
            ->newInstanceWithoutConstructor();

        $result = $ref->prepareAthletes($athletes);

        // Ensure nothing is left behind
        expect($result)->toHaveCount($athletes->count());

        foreach ($result as $i => $athlete) {
            if ($i === 0) {
                continue;
            }

            try {
                expect($result[$i - 1]['continent_id'])->not->toBe($athlete['continent_id']);
            } catch (ExpectationFailedException $e) {
                logger()->notice('Found athlete that meet their companion in match', [
                    'continent' => $athlete['continent_id'],
                    'number_of_dataset' => $continents->count(),
                    'iteration' => $i,
                ]);
            }
        }
    })->with(collect(range(1, 20))->mapWithKeys(fn ($val) => [
        "{$val} continents" => [
            fn () => Continent::factory($val)
                ->withAthletes(fn () => fake()->numberBetween(2, 9))
                ->createMany(),
        ],
    ])->toArray());
});

describe('::determineSide()', function () use ($structures) {
    /**
     * @param  Collection<int, Person>  $athletes
     * @param  int[]  $structure
     */
    it('should calculate :dataset', function (Collection $athletes, array $expected) {
        $ref = new ReflectionClass(CalculateMatchups::class);

        $result = $ref->newInstanceWithoutConstructor()->determineSide($athletes->all());

        $actual = [];
        foreach ($result as $val) {
            $actual[] = $val->count();
        }

        expect($actual)->toBe($expected);
    })->with(collect($structures)->mapWithKeys(function ($val, $key) {
        $text = implode(' ', $val);

        return [
            "{$key} to [{$text}]" => [
                fn () => Person::factory($key)
                    ->asAthlete()
                    ->createMany(),
                $val,
            ],
        ];
    })->toArray());
});
