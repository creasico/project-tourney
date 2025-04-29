<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Models\Classification;
use App\Models\Matchup;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'matches';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('match.plural');
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
                Group::make('division.id')
                    ->label(trans('match.field.division'))
                    ->getTitleFromRecordUsing(fn (Matchup $record) => $record->division->label)
                    ->orderQueryUsing(fn (Builder $query, string $direction) => $query->orderBy(
                        Classification::query()->select('order')->whereColumn('classifications.id', 'matchups.class_id'),
                        $direction
                    ))
            )
            ->modifyQueryUsing(
                fn (Builder $query) => $query->with([
                    'blue.continent',
                    'red.continent',
                    'division',
                ])
            )
            ->columns([
                Columns\TextColumn::make('party_number')
                    ->label(trans('match.field.party_number'))
                    ->width('10%')
                    ->alignCenter(),

                Columns\TextColumn::make('round_number')
                    ->label(trans('match.field.round_number'))
                    ->width('10%')
                    ->alignCenter(),

                Columns\ColumnGroup::make(trans('participant.plural'), [
                    Columns\TextColumn::make('blue.name')
                        ->label(trans('match.side.blue'))
                        ->limit(50)
                        ->width('25%')
                        ->alignRight()
                        ->description(function (Matchup $record) {
                            /** @var \App\Models\Person */
                            $athlete = $record->blue->first();

                            return $athlete->continent->name;
                        }),

                    Columns\TextColumn::make('red.name')
                        ->label(trans('match.side.red'))
                        ->limit(50)
                        ->width('25%')
                        ->description(function (Matchup $record) {
                            /** @var \App\Models\Person */
                            $athlete = $record->red->first();

                            return $athlete?->continent->name;
                        }),
                ])->alignCenter()->wrapHeader(),

                Columns\TextColumn::make('winner.name')
                    ->label(trans('match.field.winner'))
                    ->limit(50)
                    ->width('25%')
                    ->alignCenter()
                    ->description(function (Matchup $record) {
                        /** @var \App\Models\Person */
                        $athlete = $record->red->first();

                        return $athlete?->continent->name;
                    }),

                Columns\TextColumn::make('status')
                    ->label(trans('app.field.status'))
                    ->colors([
                        'primary' => static fn (Matchup $record) => $record->status->isFinished(),
                        'success' => static fn (Matchup $record) => $record->status->isOnGoing(),
                        'warning' => static fn (Matchup $record) => $record->status->isScheduled(),
                        'info' => static fn (Matchup $record) => $record->status->isDraft(),
                    ])
                    ->formatStateUsing(static fn (Matchup $record) => $record->status->label())
                    ->width('10%')
                    ->badge()
                    ->alignCenter(),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->filters([
                Filters\SelectFilter::make('classification.gender')
                    ->label(trans('participant.field.gender'))
                    ->options(Gender::toOptions())
                    ->query(function (Builder $query, $data) {
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
                    ->query(function (Builder $query, $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->whereHas(
                            'classification',
                            fn (Builder $query) => $query->where('age_range', $data['value'])
                        );
                    }),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                ])->tooltip(trans('app.resource.action_label')),
            ]);
    }
}
