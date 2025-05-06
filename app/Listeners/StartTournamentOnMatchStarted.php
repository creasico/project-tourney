<?php

namespace App\Listeners;

use App\Events\MatchupStarted;

final class StartTournamentOnMatchStarted
{
    public function handle(MatchupStarted $event): void
    {
        if ($event->match->tournament?->is_started) {
            return;
        }

        $event->match->tournament->markAsStarted();
    }
}
