<?php

namespace App\Filament\Resources;

use App\Enums\ClassificationTerm;
use App\Filament\Resources\ContinentResource\Pages;
use App\Filament\Resources\ContinentResource\RelationManagers;
use App\Models\Builders\PersonBuilder;
use App\Models\Classification;
use App\Models\Continent;
use App\View\Navigations\GroupManage;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Infolists\Components as InfolistsComponents;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class ContinentResource extends Resource
{
    use GroupManage;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = Continent::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        $nameField = fn () => Components\TextInput::make('name')
            ->label(trans('continent.field.name'));

        return $form
            ->schema([
                Components\Section::make(trans('continent.section.info_heading'))
                    ->aside()
                    ->visibleOn('edit')
                    ->schema([
                        $nameField(),
                    ]),
                $nameField()->visibleOn('create'),
            ])
            ->columns(1);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $classes = Classification::query()
            ->oldest('term')
            ->oldest('order')
            ->get();

        $relations = [
            'managers',
            'athletes',
            'athletes as males_count' => fn (PersonBuilder $builder) => $builder->onlyMales(),
            'athletes as females_count' => fn (PersonBuilder $builder) => $builder->onlyFemales(),
        ];

        foreach ($classes as $class) {
            $slug = str($class->label)->slug('_');
            $prefix = str($class->term->name)->lower();

            $relations["athletes as {$prefix}_{$slug}_count"] = function (PersonBuilder $builder) use ($class, $prefix) {
                $builder->where("class_{$prefix}_id", $class->id);
            };
        }

        $sections = $classes->groupBy('term')->map(function ($classes, $key) {
            return InfolistsComponents\Section::make(ClassificationTerm::from($key)->label())
                ->aside()
                ->columns(2)
                ->schema(
                    $classes->map(function ($class) {
                        $slug = str($class->label)->slug('_');
                        $prefix = str($class->term->name)->lower();

                        return InfolistsComponents\TextEntry::make("{$prefix}_{$slug}_count")
                            ->label($class->label)
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->hint($class->description)
                            ->numeric();
                    })->toArray()
                );
        });

        $infolist->getRecord()->loadCount($relations);

        return $infolist
            ->schema([
                InfolistsComponents\Section::make(trans('continent.section.stat_heading'))
                    ->aside()
                    ->columns(2)
                    ->schema([
                        InfolistsComponents\TextEntry::make('managers_count')
                            ->label(trans('continent.field.managers_count'))
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->numeric(),
                        InfolistsComponents\TextEntry::make('athletes_count')
                            ->label(trans('continent.field.athletes_count'))
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->numeric(),
                    ]),
                InfolistsComponents\Section::make(trans('continent.section.info_heading'))
                    ->aside()
                    ->columns(2)
                    ->schema([
                        InfolistsComponents\TextEntry::make('males_count')
                            ->label(trans('continent.field.males_count'))
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->numeric(),
                        InfolistsComponents\TextEntry::make('females_count')
                            ->label(trans('continent.field.females_count'))
                            ->color('primary')
                            ->weight(FontWeight::Bold)
                            ->numeric(),
                    ]),
                ...$sections->toArray(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Columns\TextColumn::make('name')
                    ->label(trans('continent.field.name')),
                Columns\TextColumn::make('managers_count')
                    ->label(trans('continent.field.managers_count'))
                    ->counts(['managers'])
                    ->numeric()
                    ->width('10%')
                    ->alignCenter(),
                Columns\TextColumn::make('athletes_count')
                    ->label(trans('continent.field.athletes_count'))
                    ->counts(['athletes'])
                    ->numeric()
                    ->width('10%')
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make('view'),
                    Actions\DeleteAction::make('delete'),
                ])->tooltip(trans('app.resource.action_label')),
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
            // 'create' => Pages\CreateContinent::route('/create'),
            'view' => Pages\ViewContinent::route('/{record}'),
            'edit' => Pages\EditContinent::route('/{record}/edit'),
        ];
    }
}
