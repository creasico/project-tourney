<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContinentResource\Pages;

use App\Filament\Imports\ParticipantImporter;
use App\Filament\Resources\ContinentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContinents extends ListRecords
{
    protected static string $resource = ContinentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\ImportAction::make()
                ->label(trans('participant.action.import'))
                ->importer(ParticipantImporter::class),
        ];
    }
}
