<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\MatchSide;
use App\Events\MatchupGenerated;
use App\Models\Tournament;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class GenerateMatches implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * @param  Collection<int, \App\Models\Person>  $athletes
     */
    public function __construct(
        protected Collection $athletes,
        protected Tournament $tournament,
        protected string $classId,
        protected int $divisionId,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sides = MatchSide::cases();
        $tournament = $this->tournament->fresh();

        foreach ($this->athletes->chunk(2) as $parties) {
            /** @var \App\Models\Matchup */
            $match = $tournament->matches()->create([
                'division_id' => $this->divisionId,
                'class_id' => $this->classId,
                'is_bye' => $parties->count() === 1,
            ]);

            foreach ($parties->values() as $a => $athlete) {
                $match->attachAthlete($athlete, $sides[$a]);

                $tournament->participants()->updateExistingPivot($athlete, [
                    'match_id' => $match->id,
                ]);
            }

            event(new MatchupGenerated($match));
        }
    }
}
