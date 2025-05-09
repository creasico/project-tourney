<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\TournamentLevel;
use App\Filament\Resources\TournamentResource\Pages;
use App\Filament\Resources\TournamentResource\RelationManagers;
use App\Models\Builders\PersonBuilder;
use App\Models\Tournament;
use App\View\Navigations\GroupManage;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class TournamentResource extends Resource
{
    use GroupManage;

    protected static ?string $model = Tournament::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?int $navigationSort = 1;

    private static function configureFormInfoSection()
    {
        return Components\Section::make(trans('tournament.section.info_heading'))
            ->disabled(fn (?Tournament $record) => $record?->is_started)
            ->aside()
            ->schema([
                Components\TextInput::make('title')
                    ->label(trans('tournament.field.title'))
                    ->autofocus()
                    ->required(),

                Components\Textarea::make('description')
                    ->label(trans('tournament.field.description'))
                    ->nullable(),

                Components\Select::make('level')
                    ->label(trans('tournament.field.level'))
                    ->options(TournamentLevel::toOptions())
                    ->required(),
            ]);
    }

    private static function configureFormScheduleSection()
    {
        return Components\Section::make(trans('tournament.section.schedule_heading'))
            ->disabled(fn (?Tournament $record) => $record?->is_started)
            ->columns(2)
            ->aside()
            ->schema([
                Components\DatePicker::make('start_date')
                    ->label(trans('tournament.field.start_date'))
                    ->required(),

                Components\DatePicker::make('finish_date')
                    ->label(trans('tournament.field.finish_date'))
                    ->nullable(),
            ]);
    }

    private static function configureColumns()
    {
        return [
            Columns\TextColumn::make('title')
                ->label(trans('tournament.field.title'))
                ->description(fn (Tournament $record) => $record->description),

            Columns\ColumnGroup::make(trans('participant.plural'), [
                Columns\TextColumn::make('registered_count')
                    ->label(trans('participant.participation.registered'))
                    ->counts([
                        'participants as registered_count',
                    ])
                    ->numeric()
                    ->alignCenter()
                    ->width('10%'),

                Columns\TextColumn::make('verified_count')
                    ->label(trans('participant.participation.verified'))
                    ->counts([
                        'participants as verified_count' => fn (PersonBuilder $q) => $q->whereNotNull('verified_at'),
                    ])
                    ->numeric()
                    ->alignCenter()
                    ->width('10%'),

                Columns\TextColumn::make('disqualified_count')
                    ->label(trans('participant.participation.disqualified'))
                    ->counts([
                        'participants as disqualified_count' => fn (PersonBuilder $q) => $q->whereNotNull('disqualified_at'),
                    ])
                    ->numeric()
                    ->alignCenter()
                    ->width('10%'),
            ])->alignment(Alignment::Center)->wrapHeader(),

            Columns\ColumnGroup::make(trans('tournament.field.schedule'), [
                Columns\TextColumn::make('start_date')
                    ->label(trans('tournament.field.start_date'))
                    ->alignRight()
                    ->width('10%')
                    ->formatStateUsing(
                        static fn (Tournament $record) => $record->start_date->toFormattedDateString()
                    ),

                Columns\TextColumn::make('finish_date')
                    ->label(trans('tournament.field.finish_date'))
                    ->alignRight()
                    ->width('10%')
                    ->formatStateUsing(
                        static fn (Tournament $record) => $record->finish_date->toFormattedDateString()
                    ),
            ])->alignment(Alignment::Center)->wrapHeader(),

            Columns\TextColumn::make('status')
                ->label(trans('tournament.field.status'))
                ->colors([
                    'primary' => static fn (Tournament $record) => $record->status->isFinished(),
                    'success' => static fn (Tournament $record) => $record->status->isStarted(),
                    'warning' => static fn (Tournament $record) => $record->status->isScheduled(),
                    'info' => static fn (Tournament $record) => $record->status->isDraft(),
                ])
                ->width('10%')
                ->badge()
                ->alignCenter(),
        ];
    }

    private static function configureFilters()
    {
        return [
            // .
        ];
    }

    private static function configureRowActions()
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('publish')
                    ->hidden(fn (Tournament $record) => $record->is_published)
                    ->requiresConfirmation()
                    ->action(function (Tournament $record) {
                        $record->publish();

                        Notification::make()
                            ->success()
                            ->title(trans('tournament.notification.published_title', ['party' => $record->party_number]))
                            ->send();
                    }),

                Actions\EditAction::make('edit')
                    ->hidden(fn (Tournament $record) => $record->is_finished),

                Actions\ViewAction::make('view')
                    ->hidden(fn (Tournament $record) => ! $record->is_finished),

                Actions\DeleteAction::make('delete')
                    ->hidden(fn (Tournament $record) => $record->is_finished),
            ])->tooltip(trans('app.resource.action_label')),
        ];
    }

    private static function configureBulkActions()
    {
        return [
            Actions\BulkActionGroup::make([
                Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::configureFormInfoSection(),
                self::configureFormScheduleSection(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns(self::configureColumns())
            ->filters(self::configureFilters())
            ->actions(self::configureRowActions())
            ->bulkActions(self::configureBulkActions());
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ParticipantsRelationManager::class,
            RelationManagers\ClassesRelationManager::class,
            RelationManagers\MatchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTournaments::route('/'),
            'create' => Pages\CreateTournament::route('/create'),
            'view' => Pages\ViewTournament::route('/{record}'),
            'edit' => Pages\EditTournament::route('/{record}/edit'),
        ];
    }
}
