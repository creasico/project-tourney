<?php

use App\Events\AthletesParticipated;
use App\Events\MatchupInitialized;
use App\Listeners\InitializeMatchups;
use App\Models\Classification;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $tournament = Tournament::factory()->withClassifications(
        $class = Classification::factory()->createOne()
    )->withParticipants(
        Person::factory(5)
            ->asAthlete(withClassification: $class)
            ->withContinent()
    )->createOne();

    $this->event = new AthletesParticipated(
        $tournament,
        $class
    );
});

it('should triggers an event once', function () {
    Event::fake(MatchupInitialized::class);

    (new InitializeMatchups)->handle($this->event);

    Event::assertDispatchedTimes(MatchupInitialized::class, 1);
});
