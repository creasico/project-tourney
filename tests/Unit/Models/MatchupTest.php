<?php

declare(strict_types=1);

use App\Enums\MatchSide;
use App\Enums\PartyStatus;
use App\Events\MatchupFinished;
use App\Events\MatchupStarted;
use App\Models\Classification;
use App\Models\Division;
use App\Models\MatchParty;
use App\Models\Matchup;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Support\Facades\Event;

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

it('has one prevs match', function () {
    $model = Matchup::factory()
        ->has(
            Matchup::factory(),
            'prevs'
        )
        ->createOne();

    expect($model->prevs)->toHaveCount(1);

    $prev = $model->prevs->first();

    expect($prev)->toBeInstanceOf(Matchup::class);
});

describe('schedule', function () {
    it('dispatch event on started', function () {
        Event::fake(MatchupStarted::class);

        $model = Matchup::factory()
            ->unstarted()
            ->createOne();

        $model->markAsStarted();

        Event::assertDispatched(MatchupStarted::class, 1);
    });

    it('dispatch event on finished', function () {
        Event::fake(MatchupFinished::class);

        $model = Matchup::factory()
            ->unfinished()
            ->createOne();

        $model->markAsFinished();

        Event::assertDispatched(MatchupFinished::class, 1);
    });
});

describe('sides', function () {
    it('can get :dataset from athlete', function (Matchup $match, string $attr) {
        /** @var \App\Support\Athlete */
        $side = $match->{$attr};

        expect($side)->not->toBeNull();
        expect($side->profile)->toBeInstanceOf(Person::class);
    })->with(collect(MatchSide::cases())->mapWithKeys(function (MatchSide $side) {
        return [
            "{$side->value}_side" => [
                fn () => Matchup::factory()
                    ->withAthletes(pivot: [
                        'side' => $side,
                        'status' => PartyStatus::Queue,
                    ])
                    ->createOne(),
                "{$side->value}_side",
            ],
        ];
    })->toArray());

    it('can get :dataset from previous match', function (Matchup $match, string $attr) {
        /** @var \App\Support\Athlete */
        $side = $match->{$attr};

        expect($side)->not->toBeNull();
        expect($side->profile)->toBeInstanceOf(Matchup::class);
    })->with(collect(MatchSide::cases())->mapWithKeys(function (MatchSide $side) {
        return [
            "{$side->value}_side" => [
                fn () => Matchup::factory()
                    ->has(
                        Matchup::factory()->state(['next_side' => $side]),
                        'prevs'
                    )
                    ->createOne(),
                "{$side->value}_side",
            ],
        ];
    })->toArray());
});
