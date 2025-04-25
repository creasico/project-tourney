<?php

declare(strict_types=1);

use App\Models\Continent;

it('has many athletes', function () {
    $model = Continent::factory()
        ->withAthletes(2)
        ->createOne();

    expect($model->athletes)->toHaveCount(2);
});

it('has many managers', function () {
    $model = Continent::factory()
        ->withManagers(2)
        ->createOne();

    expect($model->managers)->toHaveCount(2);
});
