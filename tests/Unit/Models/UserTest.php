<?php

declare(strict_types=1);

use App\Models\Person;
use App\Models\User;

it('has one profile', function () {
    $model = User::factory()
        ->has(
            Person::factory(),
            'profile'
        )
        ->createOne();

    expect($model->profile)->toBeInstanceOf(Person::class);
});
