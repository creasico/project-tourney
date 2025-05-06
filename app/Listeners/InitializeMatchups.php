<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AthletesParticipated;
use App\Jobs\CalculateMatchups;

final class InitializeMatchups
{
    public function handle(AthletesParticipated $event): void
    {
        $tournament = $event->tournament->fresh();

        if ($tournament->is_draft) {
            return;
        }

        dispatch(new CalculateMatchups($tournament, $event->classId))->afterCommit();
    }
}
