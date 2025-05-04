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
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class TournamentResource extends Resource
{
    use GroupManage;

    protected static ?string $model = Tournament::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?int $navigationSort = 1;

    public static function getFormInfoSection()
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

    public static function getFormScheduleSection()
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::getFormInfoSection(),
                self::getFormScheduleSection(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
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
                    ->formatStateUsing(static fn (Tournament $record) => $record->status->label())
                    ->width('10%')
                    ->badge()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make('edit'),
                    Actions\DeleteAction::make('delete'),
                ])->tooltip(trans('app.resource.action_label')),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'edit' => Pages\EditTournament::route('/{record}/edit'),
        ];
    }
}
