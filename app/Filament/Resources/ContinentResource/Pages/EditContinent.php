<?php

namespace App\Filament\Resources\ContinentResource\Pages;

use App\Filament\Resources\ContinentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContinent extends EditRecord
{
    protected static string $resource = ContinentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
