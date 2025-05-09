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
use PHPUnit\Framework\AssertionFailedError;
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

$ranges = range(3, 50);

foreach ($ranges as $c) {
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
        "p:{$total} d:{$division} [{$exp}]" => [
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

describe('::createRounds()', function () use ($ranges) {
    /**
     * @var list<list<int>>
     */
    $charts = [
        2 => [1],
        3 => [1, 1],
        4 => [2, 1],
    ];

    $prev = [];
    $i = 0;

    foreach ($ranges as $c) {
        if (isset($charts[$c])) {
            $prev = $charts[$c];

            continue;
        }

        $i++;
        $curr = $prev;

        array_unshift($curr, $i);

        $charts[$c] = $curr;

        if ($c / 2 === $i) {
            $prev = $charts[$c];
            $i = 0;
        }
    }

    it('create rounds with :dataset', function (array $items, array $charts) {
        /** @var CalculateMatchups */
        $ref = (new ReflectionClass(CalculateMatchups::class))
            ->newInstanceWithoutConstructor();

        $rounds = $ref->createRounds($items);

        try {
            expect($rounds)->toHaveCount(
                $c = count($charts),
                sprintf('Expected to generate %d rounds, %d recieved', $c, count($rounds))
            );

            foreach ($charts as $round => $matches) {
                expect($rounds[$round]->matches)->toHaveCount(
                    $matches,
                    sprintf('Expected round %d to have %d matches', $round, $matches)
                );
            }
        } catch (AssertionFailedError $err) {
            dump(array_map(fn ($r) => $r->__debugInfo(), $rounds));

            throw $err;
        }
    })->with(collect($charts)->mapWithKeys(callback: function ($charts, $count) {
        $text = implode(' ', $charts);
        $sum = array_sum($charts);
        $rounds = count($charts);

        return [
            "p:{$count} r:{$rounds} m:{$sum} [{$text}]" => [
                fn () => Person::factory($count)
                    ->asAthlete()
                    ->withContinent()
                    ->createMany()
                    ->all(),
                $charts,
            ],
        ];
    })->all());
});

describe('::divide()', function () use ($divisions) {
    it('create match divisions with :dataset', function (int $division, array $items, array $expect) {
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
            "p:{$total} d:{$division} [{$exp}]" => [
                $division,
                fn () => Person::factory($total)->make()->pluck('name')->all(),
                $expected,
            ],
        ];
    })->all());
});

describe('::prepareAthletes()', function () {
    /** @param Collection<int, Continent> $continents */
    it('should randomize continents with :dataset', function (Collection $continents) {
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
        "c:{$val}" => [
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
    it('should distribute participants with :dataset', function (Collection $athletes, array $expected) {
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
            "p:{$key} [{$text}]" => [
                fn () => Person::factory($key)
                    ->asAthlete()
                    ->createMany(),
                $val,
            ],
        ];
    })->toArray());
});
