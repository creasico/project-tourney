<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Enums\Gender;
use App\Models\Participant;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('participant.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('continent');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->inverseRelationship('tournaments')
            ->columns([
                Columns\TextColumn::make('name')
                    ->label(fn () => trans('participant.field.name')),
                Columns\TextColumn::make('continent.name')
                    ->label(fn () => trans('continent.singular')),
                Columns\TextColumn::make('classification.label')
                    ->label(fn () => trans('participant.field.classification'))
                    ->width('14%')
                    ->alignCenter(),
                Columns\TextColumn::make('gender')
                    ->label(fn () => trans('participant.field.gender'))
                    ->formatStateUsing(fn (Participant $record) => $record->gender->label())
                    ->width('14%')
                    ->alignCenter(),
                Columns\BooleanColumn::make('participation.is_verified')
                    ->label(fn () => trans('participant.field.verified'))
                    ->width('14%')
                    ->alignCenter(),
            ])
            ->filters([
                Filters\SelectFilter::make('continent.name')
                    ->label(fn () => trans('continent.singular'))
                    ->relationship('continent', 'name')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('classification')
                    ->label(fn () => trans('classification.singular'))
                    ->relationship('classification', 'label')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('gender')
                    ->label(fn () => trans('participant.field.gender'))
                    ->options(Gender::toOptions()),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make('edit'),
                    Actions\DeleteAction::make('delete'),
                ])->tooltip(fn () => trans('app.resource.action_label')),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
