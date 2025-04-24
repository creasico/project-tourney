<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContinentResource\Pages;

use App\Filament\Resources\ContinentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewContinent extends ViewRecord
{
    protected static string $resource = ContinentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make('edit'),
        ];
    }
}
