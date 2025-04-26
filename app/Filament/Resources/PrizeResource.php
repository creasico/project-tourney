<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PrizeResource\Pages;
use App\Models\PrizePool;
use App\View\Navigations\GroupSystem;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class PrizeResource extends Resource
{
    use GroupSystem;

    protected static ?string $model = PrizePool::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('label')
                    ->label(trans('prize.field.label'))
                    ->required(),

                Components\Textarea::make('description')
                    ->label(trans('prize.field.description')),

                Components\TextInput::make('order')
                    ->label(trans('prize.field.order'))
                    ->numeric()
                    ->minValue(1),
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
                    ->label(trans('prize.field.order'))
                    ->numeric()
                    ->width(1)
                    ->alignCenter(),

                Columns\TextColumn::make('label')
                    ->label(trans('prize.field.label'))
                    ->description(fn (PrizePool $record) => $record->description),
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
            'index' => Pages\ListPrizes::route('/'),
            'create' => Pages\CreatePrize::route('/create'),
            'edit' => Pages\EditPrize::route('/{record}/edit'),
        ];
    }
}
