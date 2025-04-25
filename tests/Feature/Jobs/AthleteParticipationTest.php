<?php

declare(strict_types=1);

use App\Jobs\AthleteParticipation;
use App\Models\Person;
use App\Models\Tournament;

it('can add athletes to a tournament', function () {
    $tournament = Tournament::factory()->createOne();
    $athletes = Person::factory(5)->asAthlete()->createMany();

    (new AthleteParticipation($tournament, $athletes))->handle();

    expect($tournament->participants->pluck('id')->toArray())->toBe($athletes->pluck('id')->toArray());
});
