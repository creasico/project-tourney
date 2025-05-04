<?php

use App\Events\AthletesParticipated;
use App\Jobs\CalculateMatchups;
use App\Listeners\InitializeMatchups;
use App\Models\Tournament;
use Illuminate\Support\Facades\Bus;

it('should not dispatch the job on draft tournament', function () {
    $tournament = Tournament::factory()->createOne();

    $event = new AthletesParticipated($tournament, '');

    Bus::fake(CalculateMatchups::class);

    (new InitializeMatchups)->handle($event);

    Bus::assertNotDispatched(CalculateMatchups::class);
});

it('should dispatch calculate matchup job', function () {
    $tournament = Tournament::factory()
        ->published()
        ->withClassifications()
        ->withAthletes(count: 3)
        ->createOne();

    $event = new AthletesParticipated($tournament, $tournament->classes->first()->getKey());

    Bus::fake(CalculateMatchups::class);

    (new InitializeMatchups)->handle($event);

    Bus::assertDispatched(CalculateMatchups::class);
});
