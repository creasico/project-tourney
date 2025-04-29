<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClassificationResource\Pages;

use App\Filament\Resources\ClassificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassifications extends ListRecords
{
    protected static string $resource = ClassificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // .
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'age';
    }
}
