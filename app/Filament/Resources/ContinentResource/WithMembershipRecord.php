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
            $schema[] = Components\Select::make('class_age_id')
                ->relationship('age', 'label')
                ->preload()
                ->searchable()
                ->required()
                ->searchDebounce(500);

            $schema[] = Components\Select::make('class_weight_id')
                ->relationship('weight', 'label')
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
                ->label(trans('participant.field.name')),
            Columns\TextColumn::make('gender')
                ->label(trans('participant.field.gender'))
                ->formatStateUsing(fn (Person $record) => $record->gender->label())
                ->width('14%')
                ->alignCenter(),
        ];

        if ($forAthlete) {
            $schema[] = Columns\TextColumn::make('age.label')
                ->label(trans('classification.term.age'))
                ->width('14%')
                ->alignCenter();
            $schema[] = Columns\TextColumn::make('weight.label')
                ->label(trans('classification.term.weight'))
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
            ])->tooltip(trans('app.resource.action_label')),
        ];
    }
}
