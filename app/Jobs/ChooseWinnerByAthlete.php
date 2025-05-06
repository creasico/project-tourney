<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\PartyStatus;
use App\Events\WinnerChosen;
use App\Models\Matchup;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

final class ChooseWinnerByAthlete implements ShouldBeUnique, ShouldQueue
{
    use FailsHelper;
    use Queueable, SerializesModels;

    public function __construct(
        private Matchup $match,
        private string $athleteId,
    ) {}

    public function uniqueId(): string
    {
        return $this->match->getKey();
    }

    /**
     * @codeCoverageIgnore
     */
    private function context(): array
    {
        return [
            'match_id' => $this->match->id,
            'winner_id' => $this->athleteId,
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $match = DB::transaction(function () {
            $match = $this->match->load(['athletes', 'tournament']);

            $winner = $match->athletes->where('id', '===', $this->athleteId)->firstOrFail();
            $loser = $match->athletes->where('id', '!==', $this->athleteId)->firstOrFail();

            $match->setPartyStatus($winner, PartyStatus::Win);
            $match->setPartyStatus($loser, PartyStatus::Lose);

            $now = now();

            $match->markAsFinished($now);
            $match->tournament->participants()->updateExistingPivot($loser, [
                'knocked_at' => $now,
            ]);

            return $match->fresh();
        });

        event(new WinnerChosen($match));
    }
}
