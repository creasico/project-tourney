<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\MatchBye;
use App\Events\AthletesParticipated;
use App\Models\Tournament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AthletesParticipation implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, \App\Models\Person>  $athletes
     */
    public function __construct(
        protected Tournament $tournament,
        protected Collection $athletes,
        protected bool $shoudVerify = false,
    ) {
        // .
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $verify = $this->shoudVerify ? now() : null;

            foreach ($this->athletes->groupBy('class_id') as $classId => $athletes) {
                $this->tournament->participants()->attach($athletes, [
                    'verified_at' => $verify,
                ]);

                $this->tournament->classes()->attach($classId, [
                    'division' => $athletes->count(),
                    'bye' => MatchBye::Up,
                ]);

                event(new AthletesParticipated($this->tournament, $classId));
            }
        });
    }
}
