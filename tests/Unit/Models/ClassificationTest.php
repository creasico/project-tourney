<?php

declare(strict_types=1);

use App\Models\Classification;
use App\Models\Tournament;
use App\Models\TournamentDivision;

test('has many tournaments', function () {
    $model = Classification::factory()
        ->hasAttached(
            Tournament::factory(),
            [],
            'tournaments',
        )
        ->createOne();

    expect($model->tournaments)->toHaveCount(1);

    $tournament = $model->tournaments->first();

    expect($tournament)->toBeInstanceOf(Tournament::class);
    expect($tournament->division)->toBeInstanceOf(TournamentDivision::class);
});
