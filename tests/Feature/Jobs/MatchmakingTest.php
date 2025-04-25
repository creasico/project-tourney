<?php

declare(strict_types=1);

use App\Jobs\Matchmaking;
use App\Jobs\ProceedMatchmaking;
use App\Models\Tournament;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;

it('should dipatch matchmaking job in batch', function () {
    Bus::fake(ProceedMatchmaking::class);

    $tournament = Tournament::factory()
        ->withParticipants()
        ->createOne();

    $job = (new Matchmaking($tournament))->withFakeQueueInteractions();

    $job->handle();

    $job->assertNotFailed();

    Bus::assertBatched(function (PendingBatch $batch) {
        expect($batch->jobs)->toHaveCount(1);

        return true;
    });
});
