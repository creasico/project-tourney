<?php

declare(strict_types=1);

use App\Models\Continent;
use App\Models\Person;

test('has many athletes', function () {
    $model = Continent::factory()
        ->has(
            Person::factory(2)->asAthlete(),
            'athletes'
        )
        ->createOne();

    expect($model->athletes)->toHaveCount(2);
});

test('has many managers', function () {
    $model = Continent::factory()
        ->has(
            Person::factory(2)->asManager(),
            'managers'
        )
        ->createOne();

    expect($model->managers)->toHaveCount(2);
});
