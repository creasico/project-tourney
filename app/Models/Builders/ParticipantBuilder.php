<?php

namespace App\Models\Builders;

use App\Enums\ParticipantRole;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin \App\Models\Person
 */
class ParticipantBuilder extends Builder
{
    public function onlyAthletes()
    {
        return $this->where('role', ParticipantRole::Athlete);
    }

    public function onlyManagers()
    {
        return $this->where('role', ParticipantRole::Manager);
    }
}
