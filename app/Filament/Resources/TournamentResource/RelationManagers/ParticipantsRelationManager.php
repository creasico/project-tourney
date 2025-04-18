<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Enums\Gender;
use App\Models\Person;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
                Columns\TextColumn::make('age.label')
                    ->label(fn () => trans('classification.term.age'))
                    ->width('14%')
                    ->alignCenter(),
                Columns\TextColumn::make('weight.label')
                    ->label(fn () => trans('classification.term.weight'))
                    ->width('14%')
                    ->alignCenter(),
                Columns\TextColumn::make('gender')
                    ->label(fn () => trans('participant.field.gender'))
                    ->formatStateUsing(fn (Person $record) => $record->gender->label())
                    ->width('10%')
                    ->alignCenter(),
                Columns\IconColumn::make('participation.is_verified')
                    ->label(fn () => trans('participant.participation.verification'))
                    ->width('10%')
                    ->boolean()
                    ->false(color: 'gray')
                    ->alignCenter(),
                Columns\IconColumn::make('participation.is_disqualified')
                    ->label(fn () => trans('participant.participation.disqualification'))
                    ->width('10%')
                    ->boolean()
                    ->true(color: 'danger')
                    ->false(color: 'gray')
                    ->alignCenter(),
            ])
            ->filters([
                Filters\SelectFilter::make('continent.name')
                    ->label(fn () => trans('continent.singular'))
                    ->relationship('continent', 'name')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('age')
                    ->label(fn () => trans('classification.term.age'))
                    ->relationship('age', 'label')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('weight')
                    ->label(fn () => trans('classification.term.weight'))
                    ->relationship('weight', 'label')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('gender')
                    ->label(fn () => trans('participant.field.gender'))
                    ->options(Gender::toOptions()),
            ])
            ->headerActions([
                Actions\AttachAction::make('attach')
                    ->label(fn () => trans('participant.participation.registration'))
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->onlyAthletes())
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('verify')
                        ->label(fn () => trans('participant.action.verify'))
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function (Person $participant) {
                            $this->getOwnerRecord()->verify($participant);
                        })
                        ->visible(function (Person $participant) {
                            return ! $participant->participation->is_verified;
                        }),
                    Actions\Action::make('disqualify')
                        ->label(fn () => trans('participant.action.disqualify'))
                        ->icon('heroicon-o-x-mark')
                        ->requiresConfirmation()
                        ->action(function (Person $participant) {
                            $this->getOwnerRecord()->disqualify($participant);
                        })
                        ->visible(function (Person $participant) {
                            return ! $participant->participation->is_disqualified;
                        }),
                    Actions\DissociateAction::make('deregister')
                        ->label(fn () => trans('participant.action.deregister'))
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->visible(function () {
                            return ! $this->getOwnerRecord()->is_started;
                        }),
                ])->tooltip(fn () => trans('app.resource.action_label')),
            ])
            ->bulkActions([
                Actions\BulkAction::make('bulk_verify')
                    ->label(fn () => trans('participant.action.bulk_verify'))
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each(function (Person $participant) {
                        $this->getOwnerRecord()->verify($participant);
                    })),
                Actions\BulkAction::make('bulk_disqualify')
                    ->label(fn () => trans('participant.action.bulk_disqualify'))
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each(function (Person $participant) {
                        $this->getOwnerRecord()->disqualify($participant);
                    })),
                Actions\DissociateBulkAction::make('bulk_deregister')
                    ->label(fn () => trans('participant.action.bulk_deregister'))
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ]);
    }
}
