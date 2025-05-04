<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Enums\MatchBye;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'classes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('classification.plural');
    }

    /**
     * @param  \App\Models\Tournament  $ownerRecord
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if ($ownerRecord->participants->isEmpty()) {
            return false;
        }

        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function form(Form $form): Form
    {
        $form->getRecord()->loadCount(['athletes']);

        return $form
            ->schema([
                Components\Select::make('bye')
                    ->label(trans('match.field.bye'))
                    ->options(MatchBye::toOptions())
                    ->required(),

                Components\TextInput::make('division')
                    ->label(trans('match.field.division'))
                    ->required()
                    ->numeric()
                    ->minValue(3)
                    ->maxValue(fn (Get $get) => $get('athletes_count')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display')
            ->defaultSort('order')
            ->columns($this->configureColumns())
            ->filters($this->configureFilters())
            ->headerActions($this->configureHeaderActions())
            ->actions([
                Actions\ActionGroup::make($this->configureRowActions())
                    ->tooltip(trans('app.resource.action_label')),
            ])
            ->bulkActions($this->configureBulkActions());
    }

    private function configureColumns(): array
    {
        return [
            Columns\TextColumn::make('display')
                ->label(trans('classification.singular')),

            Columns\TextColumn::make('bye')
                ->label(trans('match.field.bye'))
                ->width('10%')
                ->alignCenter(),

            Columns\TextColumn::make('division')
                ->label(trans('match.field.division'))
                ->width('10%')
                ->alignCenter(),
        ];
    }

    private function configureFilters(): array
    {
        return [];
    }

    private function configureHeaderActions(): array
    {
        return [];
    }

    private function configureRowActions(): array
    {
        return [
            Actions\EditAction::make()
                ->afterFormValidated(function () {}),
        ];
    }

    private function configureBulkActions(): array
    {
        return [
            Actions\DeleteBulkAction::make(),
        ];
    }
}
