<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\UnprocessableMatchupException;
use App\Models\Classification;

/**
 * @property \App\Models\Tournament $tournament
 * @property string $classId
 */
trait ClassifiedAthletes
{
    private function classifiedAthletes(): Classification
    {
        $class = $this->tournament->withClassifiedAthletes()
            ->where('class_id', $this->classId)
            ->first();

        if ($class === null) {
            throw new UnprocessableMatchupException("Class {$this->classId} not found");
        }

        if ($class->athletes->isEmpty()) {
            throw new UnprocessableMatchupException('No athletes found');
        }

        return $class;
    }
}
