<?php

namespace App\Filament\Resources\ContinentResource\RelationManagers;

use App\Models\Participant;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ManagersRelationManager extends RelationManager
{
    protected static string $relationship = 'managers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('continent.membership.managers');
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
            ->columns([
                Columns\TextColumn::make('name')
                    ->label(fn () => trans('participant.field.name')),
                Columns\TextColumn::make('gender')
                    ->label(fn () => trans('participant.field.gender'))
                    ->formatStateUsing(fn (Participant $record) => $record->gender->label())
                    ->width('14%')
                    ->alignCenter(),
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
