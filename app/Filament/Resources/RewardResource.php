<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardResource\Pages;
use App\Models\Reward;
use App\View\Navigations\GroupSystem;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class RewardResource extends Resource
{
    use GroupSystem;

    protected static ?string $model = Reward::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('label')
                    ->label(fn () => trans('reward.field.label'))
                    ->required(),
                Components\Textarea::make('description')
                    ->label(fn () => trans('reward.field.description')),
                Components\TextInput::make('order')
                    ->label(fn () => trans('reward.field.order'))
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
                    ->label(fn () => trans('reward.field.order'))
                    ->numeric()
                    ->width(1)
                    ->alignCenter(),
                Columns\TextColumn::make('label')
                    ->label(fn () => trans('reward.field.label'))
                    ->description(fn (Reward $record) => $record->description),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make('edit'),
                    Actions\DeleteAction::make('delete'),
                ])->tooltip(fn () => trans('app.resource.action_label')),
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
            'index' => Pages\ListRewards::route('/'),
            'create' => Pages\CreateReward::route('/create'),
            'edit' => Pages\EditReward::route('/{record}/edit'),
        ];
    }
}
