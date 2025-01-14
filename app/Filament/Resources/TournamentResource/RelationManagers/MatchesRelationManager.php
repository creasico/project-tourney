<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
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
                Components\TextInput::make('party')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('party')
            ->columns([
                Columns\TextColumn::make('party'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
