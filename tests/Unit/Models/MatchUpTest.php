<?php

declare(strict_types=1);
use App\Enums\MatchSide;
use App\Enums\MatchStatus;
use App\Models\Classification;
use App\Models\MatchParty;
use App\Models\MatchUp;
use App\Models\Person;
use App\Models\Tournament;

test('belongs to tournament', function () {
    $model = MatchUp::factory()
        ->for(
            Tournament::factory(),
        )
        ->createOne();

    expect($model->tournament)->toBeInstanceOf(Tournament::class);
});

test('belongs to classification', function () {
    $model = MatchUp::factory()
        ->for(
            Classification::factory(),
        )
        ->createOne();

    expect($model->classification)->toBeInstanceOf(Classification::class);
});

test('belongs to many participants', function () {
    $model = MatchUp::factory()
        ->hasAttached(
            Person::factory(),
            [
                'side' => MatchSide::Red,
                'round' => 1,
                'status' => MatchStatus::Queue,
            ],
            'participants'
        )
        ->createOne();

    expect($model->participants)->toHaveCount(1);

    $participant = $model->participants->first();

    expect($participant)->toBeInstanceOf(Person::class);
    expect($participant->party)->toBeInstanceOf(MatchParty::class);
    expect($participant->party->side)->toBe(MatchSide::Red);
    expect($participant->party->status)->toBe(MatchStatus::Queue);
});

test('belongs to next match', function () {
    $model = MatchUp::factory()
        ->for(
            MatchUp::factory(),
            'next'
        )
        ->createOne();

    expect($model->next)->toBeInstanceOf(MatchUp::class);
});
