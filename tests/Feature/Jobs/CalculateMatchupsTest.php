<?php

declare(strict_types=1);

use App\Events\MatchupInitialized;
use App\Exceptions\UnprocessableMatchupException;
use App\Jobs\CalculateMatchups;
use App\Models\Continent;
use App\Models\Person;
use App\Models\Tournament;
use App\Support\Matchup;
use App\Support\Round;
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

describe('::createRounds()', function () {
    $charts = [
        2 => [1],
        3 => [1, 1],
        4 => [2, 1],
        5 => [1, 2, 1],
        6 => [2, 2, 1],
        7 => [3, 2, 1],
        8 => [4, 2, 1],
        9 => [1, 4, 2, 1],
        10 => [2, 4, 2, 1],
        11 => [3, 4, 2, 1],
        12 => [4, 4, 2, 1],
        13 => [5, 4, 2, 1],
        14 => [6, 4, 2, 1],
        15 => [7, 4, 2, 1],
        16 => [8, 4, 2, 1],
        17 => [1, 8, 4, 2, 1],
        18 => [2, 8, 4, 2, 1],
        19 => [3, 8, 4, 2, 1],
        20 => [4, 8, 4, 2, 1],
        21 => [5, 8, 4, 2, 1],
        22 => [6, 8, 4, 2, 1],
        23 => [7, 8, 4, 2, 1],
        24 => [8, 8, 4, 2, 1],
        25 => [9, 8, 4, 2, 1],
        26 => [10, 8, 4, 2, 1],
        27 => [11, 8, 4, 2, 1],
        28 => [12, 8, 4, 2, 1],
        29 => [13, 8, 4, 2, 1],
        30 => [14, 8, 4, 2, 1],
        31 => [15, 8, 4, 2, 1],
        32 => [16, 8, 4, 2, 1],
        33 => [1, 16, 8, 4, 2, 1],
        34 => [2, 16, 8, 4, 2, 1],
        35 => [3, 16, 8, 4, 2, 1],
        36 => [4, 16, 8, 4, 2, 1],
        37 => [5, 16, 8, 4, 2, 1],
        38 => [6, 16, 8, 4, 2, 1],
        39 => [7, 16, 8, 4, 2, 1],
        40 => [8, 16, 8, 4, 2, 1],
        41 => [9, 16, 8, 4, 2, 1],
        42 => [10, 16, 8, 4, 2, 1],
        43 => [11, 16, 8, 4, 2, 1],
        44 => [12, 16, 8, 4, 2, 1],
        45 => [13, 16, 8, 4, 2, 1],
        46 => [14, 16, 8, 4, 2, 1],
        47 => [15, 16, 8, 4, 2, 1],
        48 => [16, 16, 8, 4, 2, 1],
        49 => [17, 16, 8, 4, 2, 1],
        50 => [18, 16, 8, 4, 2, 1],
    ];

    it('creates rounds with :dataset', function (array $items, array $charts) {
        /** @var CalculateMatchups */
        $ref = (new ReflectionClass(CalculateMatchups::class))
            ->newInstanceWithoutConstructor();

        $rounds = $ref->createRounds($items);

        $dump = collect($rounds)->reduce(function (array $out, Round $round) {
            $out[] = (object) [
                'participants' => collect($round->participants)->mapWithKeys(fn ($p) => [
                    $p->id => sprintf('%s(%s)', $p::class, $p->side?->value ?? 'none'),
                ])->toArray(),
                'matches' => collect($round->matches)->map(fn (Matchup $m) => (object) [
                    'id' => $m->id,
                    'index' => $m->index,
                    'gap' => $m->gap,
                    'round' => $m->round,
                    'isBye' => $m->isBye,
                    'nextId' => $m->nextId,
                    'nextSide' => $m->nextSide->value,
                    'isHidden' => $m->isHidden,
                    'party' => collect($m->party)->mapWithKeys(fn ($p) => [
                        $p->id => $p::class,
                    ])->toArray(),
                ])->all(),
            ];

            return $out;
        }, []);

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
            dump($dump);

            throw $err;
        }
    })->with(collect($charts)->mapWithKeys(function ($charts, $count) {
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
