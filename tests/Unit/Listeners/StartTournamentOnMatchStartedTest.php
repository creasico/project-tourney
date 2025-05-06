<?php

declare(strict_types=1);

use App\Events\MatchupStarted;
use App\Events\TournamentStarted;
use App\Listeners\StartTournamentOnMatchStarted;
use App\Models\Matchup;
use App\Models\Tournament;
use Illuminate\Support\Facades\Event;

it('starts the tournament when a match started', function () {
    Event::fake(TournamentStarted::class);

    $model = Matchup::factory()
        ->unstarted()
        ->for(
            Tournament::factory()->unstarted(),
            'tournament'
        )
        ->createOne();

    $model->markAsStarted();

    $event = new MatchupStarted($model->fresh());

    (new StartTournamentOnMatchStarted)->handle($event);

    Event::assertDispatched(TournamentStarted::class, 1);
});

it('do nothing when the tournament is already started', function () {
    Event::fake(TournamentStarted::class);

    $model = Matchup::factory()
        ->unstarted()
        ->for(
            Tournament::factory()->published(started: true),
            'tournament'
        )
        ->createOne();

    $model->markAsStarted();

    $event = new MatchupStarted($model->fresh());

    (new StartTournamentOnMatchStarted)->handle($event);

    Event::assertNotDispatched(TournamentStarted::class);
});
