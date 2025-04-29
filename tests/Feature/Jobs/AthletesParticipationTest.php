<?php

declare(strict_types=1);

use App\Events\AthletesParticipated;
use App\Jobs\AthletesParticipation;
use App\Listeners\InitializeMatchups;
use App\Models\Classification;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Support\Facades\Event;

it('can add athletes to a tournament', function () {
    Event::fake(AthletesParticipated::class);

    $class = Classification::factory()->createOne();
    $tournament = Tournament::factory()->createOne();
    $athletes = Person::factory(5)->asAthlete(withClassification: $class)->createMany();

    (new AthletesParticipation($tournament, $athletes))->handle();

    expect($tournament->participants->pluck('id')->toArray())->toBe($athletes->pluck('id')->toArray());

    Event::assertDispatchedTimes(AthletesParticipated::class, 1);
    Event::assertDispatched(function (AthletesParticipated $event) use ($tournament, $class) {
        expect($tournament)->id->toBe($event->tournament->id);
        expect($class)->id->toBe($event->classId);

        return true;
    });

    Event::assertListening(AthletesParticipated::class, InitializeMatchups::class);
});
