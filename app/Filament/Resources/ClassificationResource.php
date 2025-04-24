<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Filament\Resources\ClassificationResource\Pages;
use App\Models\Classification;
use App\View\Navigations\GroupSystem;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Grouping\Group;
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
                Components\TextInput::make('label')
                    ->label(trans('classification.field.label'))
                    ->required(),
                Components\Textarea::make('description')
                    ->label(trans('classification.field.description'))
                    ->required(),
                Components\Radio::make('gender')
                    ->label(trans('participant.field.gender'))
                    ->options(Gender::toOptions())
                    ->enum(Gender::class)
                    ->required(),
                Components\Select::make('age_range')
                    ->label(trans('classification.field.age_range'))
                    ->options(AgeRange::toOptions())
                    ->enum(AgeRange::class)
                    ->required(),
                Components\TextInput::make('weight_range')
                    ->label(trans('classification.field.weight_range'))
                    ->hint('Gunakan format "XX-XX" untuk menunjukan rentang berat badan')
                    ->required(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->defaultGroup(
                Group::make('age_range')
                    ->label(trans('classification.field.age_range'))
                    ->getTitleFromRecordUsing(fn (Classification $record) => $record->age_range->label())
            )
            ->columns([
                Columns\TextColumn::make('label')
                    ->label(trans('classification.field.label'))
                    ->description(fn (Classification $record) => $record->description),
                Columns\TextColumn::make('gender')
                    ->label(trans('participant.field.gender'))
                    ->formatStateUsing(fn (Classification $record) => $record->gender->label()),
                Columns\TextColumn::make('weight_range')
                    ->label(trans('classification.field.weight_range')),
            ])
            ->filters([
                Filters\SelectFilter::make('gender')
                    ->label(trans('participant.field.gender'))
                    ->options(Gender::toOptions()),
                Filters\SelectFilter::make('age_range')
                    ->label(trans('classification.field.age_range'))
                    ->options(AgeRange::toOptions()),
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
            // 'create' => Pages\CreateClassification::route('/create'),
            // 'edit' => Pages\EditClassification::route('/{record}/edit'),
        ];
    }
}
