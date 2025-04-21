<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Filament\Resources\ClassificationResource\Pages;
use App\Models\Classification;
use App\View\Navigations\GroupSystem;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class ClassificationResource extends Resource
{
    use GroupSystem;

    protected static ?string $model = Classification::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('label'),
                Components\Textarea::make('description'),
                Components\Radio::make('gender')->options(Gender::toOptions()),
                Components\TextInput::make('order'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                Columns\TextColumn::make('order')
                    ->label(trans('classification.field.order'))
                    ->numeric()
                    ->width(1)
                    ->alignCenter(),
                Columns\TextColumn::make('label')
                    ->label(trans('classification.field.label'))
                    ->description(fn (Classification $record) => $record->description),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make('edit'),
                    Actions\DeleteAction::make('delete'),
                ])->tooltip(trans('app.resource.action_label')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassifications::route('/'),
            'create' => Pages\CreateClassification::route('/create'),
            'edit' => Pages\EditClassification::route('/{record}/edit'),
        ];
    }
}
