<?php

namespace App\Filament\Resources\ContinentResource;

use App\Enums\Gender;
use App\Models\Participant;
use Filament\Forms\Components;
use Filament\Tables\Actions;
use Filament\Tables\Columns;

trait WithMembershipRecord
{
    private function getMembershipFormSchema(bool $forAthlete = false)
    {
        $schema = [
            Components\TextInput::make('name')
                ->label(fn () => trans('participant.field.name'))
                ->required(),
            Components\Radio::make('gender')
                ->label(fn () => trans('participant.field.gender'))
                ->options(Gender::toOptions())
                ->required(),
        ];

        if ($forAthlete) {
            $schema[] = Components\BelongsToSelect::make('class_id')
                ->relationship('classification', 'label')
                ->preload()
                ->searchable()
                ->required()
                ->searchDebounce(500);
        }

        return $schema;
    }

    private function getMembershipTableColumns(bool $forAthlete = false)
    {
        $schema = [
            Columns\TextColumn::make('name')
                ->label(fn () => trans('participant.field.name')),
            Columns\TextColumn::make('gender')
                ->label(fn () => trans('participant.field.gender'))
                ->formatStateUsing(fn (Participant $record) => $record->gender->label())
                ->width('14%')
                ->alignCenter(),
        ];

        if ($forAthlete) {
            $schema[] = Columns\TextColumn::make('classification.label')
                ->label(fn () => trans('participant.field.classification'))
                ->width('14%')
                ->alignCenter();
        }

        return $schema;
    }

    private function getMembershipTableActions()
    {
        return [
            Actions\ActionGroup::make([
                Actions\EditAction::make('edit'),
                Actions\DeleteAction::make('delete'),
            ])->tooltip(fn () => trans('app.resource.action_label')),
        ];
    }
}
