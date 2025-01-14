<?php

namespace App\Filament\Resources;

use App\Enums\ParticipantRole;
use App\Filament\Resources\ContinentResource\Pages;
use App\Filament\Resources\ContinentResource\RelationManagers;
use App\Models\Continent;
use App\View\Navigations\GroupManage;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContinentResource extends Resource
{
    use GroupManage;

    protected static ?string $model = Continent::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make(static fn () => trans('continent.section.info_heading'))
                    ->aside()
                    ->schema([
                        Components\TextInput::make('name')
                            ->label(fn () => trans('continent.field.name')),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label(fn () => trans('continent.field.name')),
                Columns\TextColumn::make('managers_count')
                    ->label(fn () => trans('continent.field.managers_count'))
                    ->counts([
                        'managers' => fn (Builder $q) => $q->where('role', ParticipantRole::Manager),
                    ])
                    ->numeric()
                    ->width('10%')
                    ->alignCenter(),
                Columns\TextColumn::make('athletes_count')
                    ->label(fn () => trans('continent.field.athletes_count'))
                    ->counts([
                        'athletes' => fn (Builder $q) => $q->where('role', ParticipantRole::Athlete),
                    ])
                    ->numeric()
                    ->width('10%')
                    ->alignCenter(),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\ManagersRelationManager::class,
            RelationManagers\AthletesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContinents::route('/'),
            'create' => Pages\CreateContinent::route('/create'),
            'edit' => Pages\EditContinent::route('/{record}/edit'),
        ];
    }
}
