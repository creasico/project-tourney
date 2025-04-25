<?php

declare(strict_types=1);

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Models\Classification;
use App\Models\MatchGroup;
use App\Models\Person;
use App\Models\Tournament;

it('belongs to many tournaments', function () {
    $model = Classification::factory()
        ->withTournaments()
        ->createOne();

    expect($model->tournaments)->toHaveCount(1);

    $tournament = $model->tournaments->first();

    expect($tournament)->toBeInstanceOf(Tournament::class);
    expect($tournament->group)->toBeInstanceOf(MatchGroup::class);
});

it('has many athletes', function () {
    $model = Classification::factory()
        ->withAthletes()
        ->createOne();

    expect($model->athletes)->toHaveCount(1);

    $athlete = $model->athletes->first();

    expect($athlete)->toBeInstanceOf(Person::class);
});

it('concat fields as display', function () {
    $model = Classification::factory()->createOne([
        'label' => 'A',
        'gender' => Gender::Male,
        'age_range' => AgeRange::Junior,
    ]);

    expect($model->display)->toBe(implode(' ', [
        'A',
        trans('classification.age.junior'),
        trans('app.gender.male'),
    ]));
});
