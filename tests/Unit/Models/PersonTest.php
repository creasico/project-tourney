<?php

declare(strict_types=1);

use App\Enums\MatchSide;
use App\Enums\MatchStatus;
use App\Enums\MedalPrize;
use App\Models\Classification;
use App\Models\Continent;
use App\Models\MatchParty;
use App\Models\MatchUp;
use App\Models\Participation;
use App\Models\Person;
use App\Models\Tournament;
use App\Models\User;

test('belongs to user', function () {
    $model = Person::factory()
        ->for(
            User::factory(),
            'credential'
        )
        ->createOne();

    expect($model->credential)->toBeInstanceOf(User::class);
});

test('belongs to continent', function () {
    $model = Person::factory()
        ->for(
            Continent::factory(),
            'continent'
        )
        ->createOne();

    expect($model->continent)->toBeInstanceOf(Continent::class);
});

test('belongs to many matches', function () {
    $model = Person::factory()
        ->hasAttached(
            MatchUp::factory(),
            [
                'side' => MatchSide::Red,
                'round' => 1,
                'status' => MatchStatus::Queue,
            ],
            'matches'
        )
        ->createOne();

    expect($model->matches)->toHaveCount(1);

    $match = $model->matches->first();

    expect($match)->toBeInstanceOf(MatchUp::class);
    expect($match->party)->toBeInstanceOf(MatchParty::class);
});

test('could classified by weight', function () {
    $model = Person::factory()
        ->for(
            Classification::factory()->asWeight(),
            'weight'
        )
        ->createOne();

    expect($model->weight)->toBeInstanceOf(Classification::class);
});

test('could classified by age', function () {
    $model = Person::factory()
        ->for(
            Classification::factory()->asAge(),
            'age'
        )
        ->createOne();

    expect($model->age)->toBeInstanceOf(Classification::class);
});

test('belongs to many tournaments', function () {
    $model = Person::factory()
        ->hasAttached(
            Tournament::factory(),
            [
                'medal' => MedalPrize::Gold,
            ],
            'tournaments'
        )
        ->createOne();

    expect($model->tournaments)->toHaveCount(1);

    $tournament = $model->tournaments->first();

    expect($tournament)->toBeInstanceOf(Tournament::class);
    expect($tournament->participation)->toBeInstanceOf(Participation::class);
    expect($tournament->participation->medal)->toBe(MedalPrize::Gold);
});

test('could be an athlete', function () {
    $model = Person::factory()
        ->asAthlete()
        ->createOne();

    expect($model->role->isAthlete())->toBeTrue();

    $models = Person::onlyAthletes()->get();

    expect($models->first()->getKey())->toBe($model->getKey());
});

test('could be a manager', function () {
    $model = Person::factory()
        ->asManager()
        ->createOne();

    expect($model->role->isManager())->toBeTrue();

    $models = Person::onlyManagers()->get();

    expect($models->first()->getKey())->toBe($model->getKey());
});
