<?php

namespace App\Support;

use App\Enums\PartyStatus;
use App\Models\Matchup;
use App\Models\Participation;
use App\Models\Person;
use Filament\Support\Contracts\HasLabel;

class Athlete implements HasLabel
{
    public readonly string $display;

    public readonly ?string $continentName;

    public readonly ?int $drawNumber;

    public readonly ?PartyStatus $status;

    public function __construct(
        public readonly Person|Matchup $profile,
        public readonly ?Participation $participation = null,
    ) {
        if ($profile instanceof Person) {
            $this->display = $profile->name;
            $this->continentName = $profile->continent?->name;
            $this->status = $profile->party?->status;
        } else {
            $this->display = trans('match.winner_from', [
                'number' => $profile->party_number,
            ]);
        }

        $this->drawNumber = $participation?->draw_number;
    }

    public function getLabel(): ?string
    {
        return $this->display;
    }
}
