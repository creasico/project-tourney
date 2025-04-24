<?php

declare(strict_types=1);

namespace App\Models\Builders;

use App\Enums\Gender;
use App\Enums\ParticipantRole;
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
}
