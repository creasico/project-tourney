<?php

declare(strict_types=1);

use App\Enums\MatchSide;
use App\Enums\PartyStatus;
use App\Models\Classification;
use App\Models\Division;
use App\Models\MatchParty;
use App\Models\Matchup;
use App\Models\Person;
use App\Models\Tournament;

it('belongs to tournament', function () {
    $model = Matchup::factory()
        ->for(
            Tournament::factory(),
        )
        ->createOne();

    expect($model->tournament)->toBeInstanceOf(Tournament::class);
});

it('belongs to division', function () {
    $model = Matchup::factory()
        ->for(
            Division::factory(),
        )
        ->createOne();

    expect($model->division)->toBeInstanceOf(Division::class);
});

it('belongs to classification', function () {
    $model = Matchup::factory()
        ->for(
            Classification::factory(),
        )
        ->createOne();

    expect($model->classification)->toBeInstanceOf(Classification::class);
});

it('belongs to many athletes', function () {
    $model = Matchup::factory()
        ->withAthletes(pivot: [
            'side' => MatchSide::Red,
            'status' => PartyStatus::Queue,
        ])
        ->createOne();

    expect($model->athletes)->toHaveCount(1);

    $athlete = $model->athletes->first();

    expect($athlete)->toBeInstanceOf(Person::class);
    expect($athlete->party)
        ->toBeInstanceOf(MatchParty::class)
        ->side->toBe(MatchSide::Red)
        ->status->toBe(PartyStatus::Queue);
});

it('belongs to next match', function () {
    $model = Matchup::factory()
        ->for(
            Matchup::factory(),
            'next'
        )
        ->createOne();

    expect($model->next)->toBeInstanceOf(Matchup::class);
});

it('has one prev match', function () {
    $model = Matchup::factory()
        ->has(
            Matchup::factory(),
            'prev'
        )
        ->createOne();

    expect($model->prev)->toBeInstanceOf(Matchup::class);
});
