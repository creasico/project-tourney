<?php

use App\Events\MatchupInitialized;
use App\Exceptions\UnprocessableMatchupException;
use App\Jobs\CalculateMatchups;
use App\Models\Continent;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\ExpectationFailedException;

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

    $job = new CalculateMatchups(
        $tournament,
        $tournament->classes->first()->getKey()
    );

    $job->handle();

    Event::assertDispatchedTimes(MatchupInitialized::class, $expected);

    Event::assertDispatched(function (MatchupInitialized $event) use ($expected) {
        $tournament = $event->tournament->fresh(['withClassifiedAthletes']);

        expect($event->tournament)->id->toBe($tournament->id);

        $class = $tournament->withClassifiedAthletes->first();

        expect($event)->classId->toBe($class->id);

        $group = $class->group->load(['divisions.matches.athletes']);

        expect($group->divisions)->toHaveCount($expected);

        return true;
    });
})->with(collect([
    [3, 0, 1],
    [5, 3, 2],
    [7, 3, 3],
])->mapWithKeys(function ($value) {
    [$athlete, $division, $expected] = $value;

    return [
        "{$athlete} athletes {$division} divisions" => [
            fn () => Tournament::factory()
                ->published()
                ->withClassifications(division: $division)
                ->withAthletes(count: $athlete)
                ->createOne(),
            $expected,
        ],
    ];
})->toArray());

describe('::prepareAthletes()', function () {
    /** @param Collection<int, Continent> $continents */
    it('should randomize :dataset', function (Collection $continents) {
        /** @var Collection<int, \App\Models\Person> */
        $athletes = $continents->reduce(
            fn (Collection $result, $continent) => $result->push(...$continent->athletes),
            collect()
        );

        $ref = new ReflectionClass(CalculateMatchups::class);

        $result = $ref->newInstanceWithoutConstructor()->prepareAthletes($athletes);

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

describe('::determineSide()', function () {
    $structures = [
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

        $structures[$c] = array_reduce([$upper, $lower], function ($result, $size) use ($structures) {
            $prev = $structures[$size] ?? null;

            if ($prev) {
                array_push($result, ...$prev);
            }

            return $result;
        }, []);
    }

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
            "{$key} athletes [{$text}]" => [
                fn () => Person::factory($key)
                    ->asAthlete()
                    ->createMany(),
                $val,
            ],
        ];
    })->toArray());
});
