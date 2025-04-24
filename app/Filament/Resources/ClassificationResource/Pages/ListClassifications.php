<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClassificationResource\Pages;

use App\Filament\Resources\ClassificationResource;
use App\Models\Builders\ClassificationBuilder as Builder;
use Filament\Actions;
use Filament\Resources\Components\Tab;
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
            // 'age' => Tab::make(trans('classification.term.age'))
            //     ->modifyQueryUsing(fn (Builder $query) => $query->onlyAges()),
            // 'weight' => Tab::make(trans('classification.term.weight'))
            //     ->modifyQueryUsing(fn (Builder $query) => $query->onlyWeights()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'age';
    }
}
