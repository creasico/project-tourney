<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Tournament;
use App\Support\ClassifiedAthletes;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class CalculateRounds implements ShouldQueue
{
    use Batchable, Queueable, SerializesModels;
    use ClassifiedAthletes, FailsHelper;

    public function __construct(
        protected Tournament $tournament,
        protected string $classId,
        protected int $divisionId,
    ) {}

    /**
     * @codeCoverageIgnore
     */
    private function context(): array
    {
        return [
            'tournament_id' => $this->tournament->id,
            'class_id' => $this->classId,
            'division_id' => $this->divisionId,
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
