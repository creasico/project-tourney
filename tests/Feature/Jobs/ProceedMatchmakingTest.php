<?php

declare(strict_types=1);

declare(strict_types=1);

use App\Jobs\GenerateMatches;
use App\Jobs\ProceedMatchmaking;
use App\Models\Tournament;
use Illuminate\Support\Facades\Queue;

it('should dispatch generate match jobs', function () {
    Queue::fake(GenerateMatches::class);

    $tournament = Tournament::factory()
        ->withParticipants()
        ->createOne();

    $class = $tournament->classes->first();

    $job = (new ProceedMatchmaking($tournament, $class->athletes, $class->group, $class))->withFakeQueueInteractions();

    $job->handle();

    $job->assertNotFailed();

    Queue::assertPushed(GenerateMatches::class, $class->athletes->count());
});
