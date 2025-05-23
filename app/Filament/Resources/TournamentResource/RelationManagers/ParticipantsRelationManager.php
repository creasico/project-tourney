<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Imports\TournamentAthleteImport;
use App\Models\Builders\PersonBuilder;
use App\Models\Person;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @property \App\Models\Tournament $ownerRecord
 */
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
            ->defaultGroup(
                Group::make('classification.id')
                    ->label(trans('classification.singular'))
                    ->getTitleFromRecordUsing(fn (Person $record) => $record->classification->display)
            )
            ->columns($this->configureColumns())
            ->filters($this->configureFilters())
            ->headerActions($this->configureHeaderActions())
            ->actions($this->configureRowActions())
            ->bulkActions($this->configureBulkActions());
    }

    private function configureColumns(): array
    {
        return [
            Columns\TextColumn::make('draw_number')
                ->label(trans('participant.field.draw_number'))
                ->width('5%')
                ->alignCenter(),

            Columns\TextColumn::make('name')
                ->label(trans('participant.field.name')),

            Columns\TextColumn::make('continent.name')
                ->label(trans('continent.singular'))
                ->width('10%')
                ->alignCenter(),

            Columns\IconColumn::make('participation.is_verified')
                ->label(trans('participant.participation.verification'))
                ->width('10%')
                ->boolean()
                ->false(color: 'gray')
                ->alignCenter(),
        ];
    }

    private function configureFilters(): array
    {
        return [
            Filters\SelectFilter::make('continent.name')
                ->label(trans('continent.singular'))
                ->relationship('continent', 'name')
                ->searchable()
                ->preload(),

            Filters\SelectFilter::make('gender')
                ->label(trans('participant.field.gender'))
                ->options(Gender::toOptions()),

            Filters\SelectFilter::make('classification.age_range')
                ->label(trans('classification.field.age_range'))
                ->options(AgeRange::toOptions())
                ->query(function (PersonBuilder $query, $data) {
                    if (! $data['value']) {
                        return $query;
                    }

                    return $query->whereHas(
                        'classification',
                        fn (Builder $query) => $query->where('age_range', $data['value'])
                    );
                }),
        ];
    }

    private function configureHeaderActions(): array
    {
        if ($this->ownerRecord->participants()->exists()) {
            return [];
        }

        return [
            Actions\Action::make('import-athletes')
                ->label(trans('participant.action.import'))
                ->modalHeading(trans('participant.action.import'))
                ->modalSubmitActionLabel(trans('participant.action.upload_participant'))
                ->form([
                    Components\FileUpload::make('file')
                        ->storeFile(false)
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->mimeTypeMap([
                            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]),
                ])
                ->action(function (array $data) {
                    $tournament = $this->ownerRecord;

                    Excel::import(new TournamentAthleteImport(
                        tournament: $tournament
                    ), $data['file']);

                    $tournament->refresh();

                    Notification::make()
                        ->success()
                        ->title(trans('participant.notification.import_title'))
                        ->send();
                })
                ->after(function (Component $livewire): void {
                    $livewire->redirect(EditTournament::getUrl([
                        'record' => $this->ownerRecord->getKey(),
                    ]));
                }),
        ];
    }

    private function configureRowActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('verify')
                    ->label(trans('participant.action.verify'))
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->hidden(fn (Person $participant) => $this->ownerRecord->is_finished || $participant->participation->is_verified)
                    ->action(function (Person $record) {
                        $this->ownerRecord->verify($record);

                        Notification::make()
                            ->success()
                            ->title(trans('participant.notification.verified_title'))
                            ->body(trans('participant.notification.verified_body', [
                                'athlete' => $record->name,
                            ]))
                            ->send();
                    }),

                Actions\Action::make('disqualify')
                    ->label(trans('participant.action.disqualify'))
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->hidden(fn (Person $participant) => $this->ownerRecord->is_finished || $participant->participation->is_disqualified)
                    ->action(function (Person $record) {
                        $this->ownerRecord->disqualify($record);

                        Notification::make()
                            ->success()
                            ->title(trans('participant.notification.disqualified_title'))
                            ->body(trans('participant.notification.disqualified_body', [
                                'athlete' => $record->name,
                            ]))
                            ->send();
                    }),

                Actions\DetachAction::make('deregister')
                    ->label(trans('participant.action.deregister'))
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->hidden(fn () => $this->ownerRecord->is_started),
            ])->tooltip(trans('app.resource.action_label')),
        ];
    }

    private function configureBulkActions(): array
    {
        return [
            Actions\BulkAction::make('bulk_verify')
                ->label(trans('participant.action.bulk_verify'))
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->hidden(fn () => $this->ownerRecord->is_finished)
                ->action(function (Collection $records) {
                    $records->each(function (Person $participant) {
                        $this->getOwnerRecord()->verify($participant);
                    });

                    Notification::make()
                        ->success()
                        ->title(trans('participant.notification.bulk_verified_title'))
                        ->body(trans('participant.notification.bulk_verified_body', [
                            'number' => $records->count(),
                        ]))
                        ->send();
                }),

            Actions\BulkAction::make('bulk_disqualify')
                ->label(trans('participant.action.bulk_disqualify'))
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->hidden(fn () => $this->ownerRecord->is_finished)
                ->action(function (Collection $records) {
                    $records->each(function (Person $participant) {
                        $this->getOwnerRecord()->disqualify($participant);
                    });

                    Notification::make()
                        ->success()
                        ->title(trans('participant.notification.bulk_disqualified_title'))
                        ->body(trans('participant.notification.bulk_disqualified_body', [
                            'number' => $records->count(),
                        ]))
                        ->send();
                }),

            Actions\DissociateBulkAction::make('bulk_deregister')
                ->label(trans('participant.action.bulk_deregister'))
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->hidden(fn () => $this->ownerRecord->is_finished),
        ];
    }
}
