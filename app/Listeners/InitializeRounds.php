<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MatchupInitialized;
use App\Jobs\CalculateRounds;

class InitializeRounds
{
    /**
     * @codeCoverageIgnore
     */
    public function handle(MatchupInitialized $event): void
    {
        dispatch(new CalculateRounds(
            tournament: $event->tournament,
            classId: $event->classId,
            divisionId: $event->divisionId,
        ))->afterCommit();
    }
}
