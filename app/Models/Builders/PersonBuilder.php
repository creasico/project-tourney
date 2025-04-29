<?php

declare(strict_types=1);

namespace App\Models\Builders;

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Enums\ParticipantRole;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin \App\Models\Person
 */
class PersonBuilder extends Builder
{
    public function onlyAthletes()
    {
        return $this->where('role', ParticipantRole::Athlete);
    }

    public function onlyManagers()
    {
        return $this->where('role', ParticipantRole::Manager);
    }

    public function onlyMales()
    {
        return $this->where('gender', Gender::Male);
    }

    public function onlyFemales()
    {
        return $this->where('gender', Gender::Female);
    }

    public function hasAgeRange(AgeRange|int $range)
    {
        if (is_int($range)) {
            $range = AgeRange::from($range);
        }

        return $this->whereHas(
            'classification',
            fn (Builder $query) => $query->where('age_range', $range)
        );
    }

    public function haveParticipate(Tournament|string $tournament)
    {
        if ($tournament instanceof Tournament) {
            $tournament = $tournament->getKey();
        }

        return $this->whereHas(
            'tournaments',
            fn (Builder $query) => $query->where('id', $tournament)
        );
    }
}
