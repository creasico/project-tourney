<?php

declare(strict_types=1);

use App\Jobs\GenerateMatches;
use App\Models\Tournament;

it('should create matchups', function () {
    $tournament = Tournament::factory()
        ->withParticipants()
        ->createOne();

    $class = $tournament->classes->first();
    $division = $class->group->divisions()->create([
        'label' => 'Some division',
    ]);

    $job = (new GenerateMatches($class->athletes, $tournament, $class->getKey(), $division->getKey()))->withFakeQueueInteractions();

    $job->handle();

    $job->assertNotFailed();
});
