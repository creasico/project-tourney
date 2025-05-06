<?php

namespace App\Support;

use App\Models\Matchup;
use App\Models\Participation;
use App\Models\Person;

class Athlete
{
    public readonly string $display;

    public readonly ?string $continentName;

    public readonly ?int $drawNumber;

    public function __construct(
        public readonly Person|Matchup $profile,
        public readonly ?Participation $participation = null,
    ) {
        if ($profile instanceof Person) {
            $this->display = $profile->name;
            $this->continentName = $profile->continent?->name;
        } else {
            $this->display = trans('match.winner_from', [
                'number' => $profile->party_number,
            ]);
        }

        if ($participation) {
            $this->drawNumber = $participation->draw_number;
        }
    }
}
