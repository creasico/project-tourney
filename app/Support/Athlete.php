<?php

namespace App\Support;

use App\Enums\PartyStatus;
use App\Models\Matchup;
use App\Models\Participation;
use App\Models\Person;
use Filament\Support\Contracts\HasLabel;

/**
 * State class to hold participant information to diplay in the UI.
 */
class Athlete implements HasLabel
{
    public readonly string $display;

    public readonly ?string $continentName;

    public readonly ?int $drawNumber;

    public readonly PartyStatus $status;

    public readonly bool $isPerson;

    public function __construct(
        public readonly Person|Matchup $profile,
        public readonly ?Participation $participation = null,
    ) {
        $this->isPerson = $profile instanceof Person;

        if ($this->isPerson) {
            $this->display = $profile->name;
            $this->continentName = $profile->continent?->name;
            $this->status = $profile->party?->status ?? PartyStatus::Queue;
        } else {
            $this->continentName = null;
            $this->status = PartyStatus::Queue;
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

    public function canProceed(): bool
    {
        return $this->status->isQueue() || ! $this->status->isLose();
    }

    public function getAriaLabel(): string
    {
        if ($this->continentName) {
            return trans('participant.bracket_label', [
                'athlete' => $this->display,
                'continent' => $this->continentName,
            ]);
        }

        return $this->display;
    }
}
