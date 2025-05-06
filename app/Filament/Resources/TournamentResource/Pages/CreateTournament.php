<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * @property null|\App\Models\Tournament $record
 */
class CreateTournament extends CreateRecord
{
    protected static string $resource = TournamentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->getRecord(),
            ...$this->getRedirectUrlParameters(),
        ]);
    }
}
