<?php

declare(strict_types=1);

use App\Events\AthletesParticipated;
use App\Jobs\AthletesParticipation;
use App\Models\Classification;
use App\Models\Person;
use App\Models\Tournament;
use Illuminate\Support\Facades\Event;

it('can add athletes to a tournament', function () {
    Event::fake(AthletesParticipated::class);

    $class = Classification::factory()->createOne();
    $tournament = Tournament::factory()->createOne();
    $athletes = Person::factory(5)->asAthlete(createClass: false)->state([
        'class_id' => $class->id,
    ])->createMany();

    (new AthletesParticipation($tournament, $athletes))->handle();

    expect($tournament->participants->pluck('id')->toArray())->toBe($athletes->pluck('id')->toArray());

    EVent::assertDispatched(AthletesParticipated::class, 1);
});
