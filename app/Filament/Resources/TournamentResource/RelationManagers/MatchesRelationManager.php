<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Jobs\ChooseWinnerByAthlete;
use App\Models\Classification;
use App\Models\Matchup;
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
use Illuminate\Database\Eloquent\Model;

/**
 * @property \App\Models\Tournament $ownerRecord
 */
class MatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'matches';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('match.plural');
    }

    /**
     * @param  \App\Models\Tournament  $ownerRecord
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! $ownerRecord->matches()->exists()) {
            return false;
        }

        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('party_number')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('party_number')
            ->defaultSort('party_number')
            ->defaultGroup(
                Group::make('division_id')
                    ->label(trans('match.field.division'))
                    ->getTitleFromRecordUsing(fn (Matchup $record) => $record->division->label)
                // ->orderQueryUsing(fn (Builder $query, string $direction) => $query->orderBy(
                //     Classification::query()->select('order')->whereColumn('classifications.id', 'matchups.class_id'),
                //     $direction
                // ))
            )
            ->modifyQueryUsing(
                fn (Builder $query) => $query->with([
                    'blue.continent',
                    'red.continent',
                    'participations',
                    'division',
                    'winning',
                    'next',
                    'prevs',
                ])
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
            Columns\TextColumn::make('party_number')
                ->label(trans('match.field.party_number'))
                ->width('10%')
                ->size(Columns\TextColumn\TextColumnSize::Large)
                ->alignCenter(),

            Columns\TextColumn::make('round_number')
                ->label(trans('match.field.round_number'))
                ->width('10%')
                ->size(Columns\TextColumn\TextColumnSize::Large)
                ->alignCenter(),

            Columns\ColumnGroup::make(trans('participant.plural'), [
                Columns\TextColumn::make('blue_side')
                    ->label(trans('match.side.blue'))
                    ->limit(50)
                    ->width('20%')
                    ->alignRight()
                    ->description(
                        fn (Matchup $record) => $record->blue_side?->continentName
                    ),

                Columns\TextColumn::make('blue_side.drawNumber')
                    ->label('#')
                    ->width('5%')
                    ->size(Columns\TextColumn\TextColumnSize::Large)
                    ->tooltip(trans('participant.field.draw_number'))
                    ->alignCenter()
                    ->numeric(),

                Columns\TextColumn::make('red_side.drawNumber')
                    ->label('#')
                    ->width('5%')
                    ->size(Columns\TextColumn\TextColumnSize::Large)
                    ->tooltip(trans('participant.field.draw_number'))
                    ->alignCenter()
                    ->numeric(),

                Columns\TextColumn::make('red_side')
                    ->label(trans('match.side.red'))
                    ->limit(50)
                    ->width('20%')
                    ->description(
                        fn (Matchup $record) => $record->red_side?->continentName
                    ),
            ])->alignCenter()->wrapHeader(),

            Columns\TextColumn::make('winner.name')
                ->label(trans('match.field.winner'))
                ->limit(50)
                ->width('25%')
                ->alignCenter()
                ->default(fn (Matchup $record) => $record->is_draw ? 'Draw' : '-')
                ->description(function (Matchup $record) {
                    /** @var \App\Models\Person */
                    $athlete = $record->winner;

                    return $athlete?->continent->name;
                }),

            Columns\TextColumn::make('status')
                ->label(trans('app.field.status'))
                ->colors([
                    'primary' => static fn (Matchup $record) => $record->status->isFinished(),
                    'success' => static fn (Matchup $record) => $record->status->isStarted(),
                    'warning' => static fn (Matchup $record) => $record->status->isScheduled(),
                    'info' => static fn (Matchup $record) => $record->status->isDraft(),
                ])
                ->formatStateUsing(static fn (Matchup $record) => $record->status->label())
                ->width('10%')
                ->badge()
                ->alignCenter(),
        ];
    }

    private function configureFilters(): array
    {
        return [
            Filters\SelectFilter::make('classification.gender')
                ->label(trans('participant.field.gender'))
                ->options(Gender::toOptions())
                ->query(function (Builder $query, array $data) {
                    if (! $data['value']) {
                        return $query;
                    }

                    return $query->whereHas(
                        'classification',
                        fn (Builder $query) => $query->where('gender', $data['value'])
                    );
                }),

            Filters\SelectFilter::make('classification.age_range')
                ->label(trans('classification.field.age_range'))
                ->options(AgeRange::toOptions())
                ->query(function (Builder $query, array $data) {
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
        return [];
    }

    private function configureRowActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\EditAction::make()
                    ->visible(fn (Matchup $record): bool => ! $record->is_started),

                Actions\Action::make('start_match')
                    ->label(trans('match.actions.start_match'))
                    ->icon('heroicon-m-paper-airplane')
                    ->visible(fn (Matchup $record): bool => ! $record->is_started)
                    ->action(function (Matchup $record) {
                        $record->markAsStarted();

                        Notification::make()
                            ->success()
                            ->title(trans('match.notification.started_title', ['party' => $record->party_number]))
                            ->send();
                    }),

                Actions\Action::make('choose_winner')
                    ->label(trans('match.actions.choose_winner'))
                    ->icon('heroicon-m-trophy')
                    ->visible(fn (Matchup $record): bool => $record->is_going)
                    ->modalHeading(trans('match.actions.choose_winner'))
                    ->modalSubmitActionLabel(trans('match.actions.choose_winner'))
                    ->form([
                        Components\Select::make('athlete')
                            ->native(false)
                            ->options(
                                fn (Matchup $record) => $record->athletes->mapWithKeys(function ($athlete) {
                                    return [
                                        $athlete->id => "{$athlete->party->side->label()} - {$athlete->name}",
                                    ];
                                })
                            ),
                    ])
                    ->action(function (Matchup $record, array $data) {
                        dispatch_sync(new ChooseWinnerByAthlete($record, $data['athlete']));

                        $match = $record->fresh('winning');

                        Notification::make()
                            ->success()
                            ->title(trans('match.notification.winner_choosen_title', ['party' => $record->party_number]))
                            ->body(trans('match.notification.winner_choosen_body', ['athlete' => $match->winner->name]))
                            ->send();
                    }),

                Actions\Action::make('set_as_draw')
                    ->label(trans('match.actions.set_as_draw'))
                    ->requiresConfirmation()
                    ->icon('heroicon-m-scale')
                    ->visible(fn (Matchup $record): bool => $record->is_going)
                    ->action(function (Matchup $record) {
                        $record->markAsDraw();

                        Notification::make()
                            ->success()
                            ->title(trans('match.notification.marked_draw_title', ['party' => $record->party_number]))
                            ->send();
                    }),
            ])->tooltip(trans('app.resource.action_label')),
        ];
    }

    private function configureBulkActions(): array
    {
        return [];
    }
}
