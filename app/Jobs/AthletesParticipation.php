<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\MatchBye;
use App\Events\AthletesParticipated;
use App\Models\Tournament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AthletesParticipation implements ShouldQueue
{
    use FailsHelper;
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\Person>  $athletes
     */
    public function __construct(
        protected Tournament $tournament,
        protected Collection $athletes,
        protected MatchBye $bye = MatchBye::Up,
        protected bool $shoudVerify = false,
    ) {}

    /**
     * @codeCoverageIgnore
     */
    private function context(): array
    {
        return [
            'tournament_id' => $this->tournament->id,
            'athletes' => $this->athletes->all(),
            'bye' => $this->bye,
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $verify = $this->shoudVerify ? now() : null;

            foreach ($this->athletes->groupBy('class_id') as $classId => $athletes) {
                foreach ($athletes as $a => $athlete) {
                    $this->tournament->participants()->attach($athlete, [
                        'verified_at' => $verify,
                        'draw_number' => $a + 1,
                    ]);
                }

                $this->tournament->classes()->attach($classId, [
                    'division' => $athletes->count(),
                    'bye' => $this->bye,
                ]);

                event(new AthletesParticipated($this->tournament, $classId));
            }
        });
    }
}
