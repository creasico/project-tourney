<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Tournament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AthleteParticipation implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, \App\Models\Person>  $athletes
     */
    public function __construct(
        protected Tournament $tournament,
        protected Collection $athletes,
    ) {
        // .
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            foreach ($this->athletes->chunk(500) as $athletes) {
                $this->tournament->participants()->attach($athletes, []);
            }

            foreach ($this->athletes->pluck('class_id')->unique() as $classes) {
                $this->tournament->classes()->attach($classes, []);
            }
        });
    }
}
