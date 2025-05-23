<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\View\Navigations\GroupSystem;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class UserResource extends Resource
{
    use GroupSystem;

    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    private static function configureColumns()
    {
        return [
            Columns\TextColumn::make('name')
                ->label(fn () => __('filament-panels::pages/auth/edit-profile.form.name.label')),

            Columns\TextColumn::make('email')
                ->label(fn () => __('filament-panels::pages/auth/edit-profile.form.email.label')),
        ];
    }

    private static function configureFilters()
    {
        return [
            // .
        ];
    }

    private static function configureRowActions()
    {
        return [
            Actions\ActionGroup::make([
                Actions\EditAction::make('edit'),
                Actions\DeleteAction::make('delete'),
            ])
                ->tooltip(trans('app.resource.action_label'))
                ->visible(fn (User $record) => $record->isNot(auth()->user())),
        ];
    }

    private static function configureBulkActions()
    {
        return [
            Actions\BulkActionGroup::make([
                Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

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
            ->recordUrl(fn (User $record) => $record->is(auth()->user()) ? route('filament.admin.auth.profile') : null)
            ->columns(self::configureColumns())
            ->filters(self::configureFilters())
            ->actions(self::configureRowActions())
            ->bulkActions(self::configureBulkActions());
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
