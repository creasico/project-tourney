<?php

declare(strict_types=1);

use App\Enums\MatchSide;
use App\Enums\MatchStatus;
use App\Models\Classification;
use App\Models\MatchParty;
use App\Models\Matchup;
use App\Models\Person;
use App\Models\Tournament;

test('belongs to tournament', function () {
    $model = Matchup::factory()
        ->for(
            Tournament::factory(),
        )
        ->createOne();

    expect($model->tournament)->toBeInstanceOf(Tournament::class);
});

test('belongs to classification', function () {
    $model = Matchup::factory()
        ->for(
            Classification::factory(),
        )
        ->createOne();

    expect($model->classification)->toBeInstanceOf(Classification::class);
});

test('belongs to many athletes', function () {
    $model = Matchup::factory()
        ->withAthletes(pivot: [
            'side' => MatchSide::Red,
            'status' => MatchStatus::Queue,
        ])
        ->createOne();

    expect($model->athletes)->toHaveCount(1);

    $athlete = $model->athletes->first();

    expect($athlete)->toBeInstanceOf(Person::class);
    expect($athlete->party)->toBeInstanceOf(MatchParty::class);
    expect($athlete->party->side)->toBe(MatchSide::Red);
    expect($athlete->party->status)->toBe(MatchStatus::Queue);
});

test('belongs to next match', function () {
    $model = Matchup::factory()
        ->for(
            Matchup::factory(),
            'next'
        )
        ->createOne();

    expect($model->next)->toBeInstanceOf(Matchup::class);
});
