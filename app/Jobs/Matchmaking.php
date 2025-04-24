<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\MatchmakingSucceeded;
use App\Models\Tournament;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class Matchmaking implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Tournament $tournament,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $tournament = $this->tournament->fresh();

            $batch = Bus::batch(
                $tournament->classes->map(fn ($class) => new ProceedMatchmaking(
                    $tournament,
                    $class->athletes,
                    $class->group,
                    $class
                ))
            )->then(function (Batch $batch) use ($tournament) {
                event(new MatchmakingSucceeded($tournament));
            });

            $batch->name("Proceed matchmaking: {$this->tournament->title}")->dispatch();
        });
    }
}
