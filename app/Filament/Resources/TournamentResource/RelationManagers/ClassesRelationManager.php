<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Enums\Category;
use App\Enums\MatchBye;
use App\Jobs\CalculateMatchups;
use App\Models\Builders\PersonBuilder;
use App\Models\Classification;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Bus;
use Livewire\Component;

/**
 * @property \App\Models\Tournament $ownerRecord
 */
class ClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'classes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('classification.plural');
    }

    /**
     * @param  \App\Models\Tournament  $ownerRecord
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if ($ownerRecord->participants->isEmpty()) {
            return false;
        }

        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function form(Form $form): Form
    {
        $form->getRecord()->loadCount([
            'athletes' => $this->athleteQuery(),
        ]);

        return $form
            ->schema([
                Components\Select::make('category')
                    ->label(trans('category.singular'))
                    ->options(Category::toOptions())
                    ->required(),

                Components\Select::make('bye')
                    ->label(trans('match.field.bye'))
                    ->options(MatchBye::toOptions())
                    ->required(),

                Components\TextInput::make('division')
                    ->label(trans('match.field.division'))
                    ->required()
                    ->numeric()
                    ->minValue(3)
                    ->maxValue(fn (Get $get) => $get('athletes_count')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display')
            ->defaultSort('order')
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->withCount([
                        'athletes as athletes_count' => $this->athleteQuery(),
                    ])
                    ->with([
                        'athletes' => $this->athleteQuery(),
                    ])
            )
            ->columns($this->configureColumns())
            ->filters($this->configureFilters())
            ->headerActions($this->configureHeaderActions())
            ->actions($this->configureRowActions())
            ->bulkActions($this->configureBulkActions());
    }

    private function configureColumns(): array
    {
        return [
            Columns\TextColumn::make('display')
                ->label(trans('classification.singular')),

            Columns\TextColumn::make('category')
                ->label(trans('category.singular'))
                ->formatStateUsing(fn (Classification $record) => $record->category->label())
                ->width('8%'),

            Columns\TextColumn::make('bye')
                ->label(trans('match.field.bye'))
                ->formatStateUsing(fn (Classification $record) => $record->bye->label())
                ->width('8%')
                ->alignCenter(),

            Columns\TextColumn::make('division')
                ->label(trans('match.field.division'))
                ->width('10%')
                ->alignCenter(),

            Columns\TextColumn::make('athletes_count')
                ->label(trans('continent.field.athletes_count'))
                ->width('10%')
                ->alignCenter(),
        ];
    }

    private function configureFilters(): array
    {
        return [];
    }

    private function configureHeaderActions(): array
    {
        $tournament = $this->ownerRecord;

        if ($tournament->participants->isNotEmpty() && $tournament->is_started) {
            return [];
        }

        return [
            Actions\Action::make('generate_all')
                ->label(trans('match.actions.generate'))
                ->requiresConfirmation()
                ->icon('heroicon-m-arrow-path-rounded-square')
                ->action(function (Component $livewire) {
                    $user = auth()->user();

                    Bus::batch($this->ownerRecord->classes->map(
                        fn ($class) => new CalculateMatchups(
                            $this->ownerRecord,
                            $class->getKey(),
                        )
                    ))->catch(function () use ($user) {
                        Notification::make()
                            ->danger()
                            ->title(trans('match.notification.calculation_failed_title'))
                            ->body(trans('match.notification.calculation_failed_body'))
                            ->sendToDatabase($user);
                    })->then(function () use ($user) {
                        Notification::make()
                            ->success()
                            ->title(trans('match.notification.calculated_title'))
                            ->body(trans('match.notification.calculated_body'))
                            ->sendToDatabase($user);
                    })->name("Calculating matches for {$this->ownerRecord->title}")->dispatch();

                    Notification::make()
                        ->info()
                        ->title(trans('match.notification.calculating_title'))
                        ->body(trans('match.notification.calculating_body'))
                        ->send();
                }),
        ];
    }

    private function configureRowActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\EditAction::make(),

                Actions\Action::make('generate')
                    ->label(trans('match.actions.generate'))
                    ->requiresConfirmation()
                    ->icon('heroicon-m-arrow-path-rounded-square')
                    ->action(function (Component $livewire, Classification $record) {
                        $user = auth()->user();

                        try {
                            dispatch_sync(
                                new CalculateMatchups($this->ownerRecord, $record->getKey())
                            );

                            Notification::make()
                                ->success()
                                ->title(trans('match.notification.calculated_title'))
                                ->body(trans('match.notification.calculated_body'))
                                ->send();
                        } catch (\Throwable $_) {
                            Notification::make()
                                ->danger()
                                ->title(trans('match.notification.calculation_failed_title'))
                                ->body(trans('match.notification.calculation_failed_body'))
                                ->sendToDatabase($user);
                        }
                    }),
            ])->tooltip(trans('app.resource.action_label')),
        ];
    }

    private function configureBulkActions(): array
    {
        return [
            Actions\DeleteBulkAction::make(),
        ];
    }

    private function athleteQuery()
    {
        return fn (HasMany|PersonBuilder $query) => $query->haveParticipate($this->ownerRecord);
    }
}
