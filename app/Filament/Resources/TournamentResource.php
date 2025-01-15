<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentResource\Pages;
use App\Filament\Resources\TournamentResource\RelationManagers;
use App\Models\Tournament;
use App\View\Navigations\GroupManage;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TournamentResource extends Resource
{
    use GroupManage;

    protected static ?string $model = Tournament::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make(static fn () => trans('tournament.section.info_heading'))
                    ->aside()
                    ->schema([
                        Components\TextInput::make('title')
                            ->label(fn () => trans('tournament.field.title'))
                            ->disabled(fn (Tournament $record) => $record->is_started)
                            ->required(),
                        Components\Textarea::make('description')
                            ->label(fn () => trans('tournament.field.description'))
                            ->disabled(fn (Tournament $record) => $record->is_started)
                            ->nullable(),
                    ]),
                Components\Section::make(static fn () => trans('tournament.section.schedule_heading'))
                    ->aside()
                    ->columns(2)
                    ->schema([
                        Components\DatePicker::make('start_date')
                            ->label(fn () => trans('tournament.field.start_date'))
                            ->disabled(fn (Tournament $record) => $record->is_started)
                            ->required(),
                        Components\DatePicker::make('finish_date')
                            ->label(fn () => trans('tournament.field.finish_date'))
                            ->disabled(fn (Tournament $record) => $record->is_finished)
                            ->nullable(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Columns\TextColumn::make('title')
                    ->label(fn () => trans('tournament.field.title'))
                    ->description(fn (Tournament $record) => $record->description),
                Columns\ColumnGroup::make(fn () => trans('tournament.field.participants'), [
                    Columns\TextColumn::make('registered_count')
                        ->label(fn () => trans('tournament.field.registered_count'))
                        ->counts([
                            'participants as registered_count',
                        ])
                        ->numeric()
                        ->alignCenter()
                        ->width('10%'),
                    Columns\TextColumn::make('verified_count')
                        ->label(fn () => trans('tournament.field.verified_count'))
                        ->counts([
                            'participants as verified_count' => fn (Builder $q) => $q->whereNotNull('verified_at'),
                        ])
                        ->numeric()
                        ->alignCenter()
                        ->width('10%'),
                ])->alignment(Alignment::Center)->wrapHeader(),
                Columns\ColumnGroup::make(fn () => trans('tournament.field.schedule'), [
                    Columns\TextColumn::make('start_date')
                        ->label(fn () => trans('tournament.field.start_date'))
                        ->formatStateUsing(
                            static fn (Tournament $record) => $record->start_date->toFormattedDateString()
                        )
                        ->width('10%'),
                    Columns\TextColumn::make('finish_date')
                        ->label(fn () => trans('tournament.field.finish_date'))
                        ->formatStateUsing(
                            static fn (Tournament $record) => $record->finish_date->toFormattedDateString()
                        )
                        ->width('10%'),
                ])->alignment(Alignment::Center)->wrapHeader(),
                Columns\TextColumn::make('status')
                    ->label(fn () => trans('tournament.field.status'))
                    ->colors([
                        'primary' => static fn (Tournament $record) => $record->status->isFinished(),
                        'success' => static fn (Tournament $record) => $record->status->isOnGoing(),
                        'warning' => static fn (Tournament $record) => $record->status->isScheduled(),
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
                ])->tooltip(fn () => trans('app.resource.action_label')),
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
