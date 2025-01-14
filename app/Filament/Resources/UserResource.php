<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\View\Navigations\GroupSetting;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class UserResource extends Resource
{
    use GroupSetting;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('name'),
                Components\TextInput::make('email'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Columns\TextColumn::make('name')
                    ->label(fn () => __('filament-panels::pages/auth/edit-profile.form.name.label')),
                Columns\TextColumn::make('email')
                    ->label(fn () => __('filament-panels::pages/auth/edit-profile.form.email.label')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make('edit')
                    ->visible(fn (User $record) => $record->isNot(auth()->user())),
            ])
            ->recordUrl(fn (User $record) => $record->is(auth()->user()) ? route('filament.admin.auth.profile') : null)
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
