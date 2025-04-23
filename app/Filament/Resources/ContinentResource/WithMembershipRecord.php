<?php

namespace App\Filament\Resources\ContinentResource;

use App\Enums\Gender;
use App\Models\Person;
use Filament\Forms\Components;
use Filament\Tables\Actions;
use Filament\Tables\Columns;

trait WithMembershipRecord
{
    private function getMembershipFormSchema(bool $forAthlete = false)
    {
        $schema = [
            Components\TextInput::make('name')
                ->label(trans('participant.field.name'))
                ->required(),
            Components\Radio::make('gender')
                ->label(trans('participant.field.gender'))
                ->options(Gender::toOptions())
                ->required(),
        ];

        if ($forAthlete) {
            // TODO: Show age and weight class for athletes
        }

        return $schema;
    }

    private function getMembershipTableColumns(bool $forAthlete = false)
    {
        $schema = [
            Columns\TextColumn::make('name')
                ->label(trans('participant.field.name')),
            Columns\TextColumn::make('gender')
                ->label(trans('participant.field.gender'))
                ->formatStateUsing(fn (Person $record) => $record->gender->label())
                ->width('14%')
                ->alignCenter(),
        ];

        if ($forAthlete) {
            $schema[] = Columns\TextColumn::make('classification.label')
                ->label(trans('classification.field.weight_range'));
        }

        return $schema;
    }

    private function getMembershipTableActions()
    {
        return [
            Actions\ActionGroup::make([
                Actions\EditAction::make('edit'),
                Actions\DeleteAction::make('delete'),
            ])->tooltip(trans('app.resource.action_label')),
        ];
    }
}
