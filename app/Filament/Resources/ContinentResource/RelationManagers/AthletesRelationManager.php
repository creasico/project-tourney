<?php

namespace App\Filament\Resources\ContinentResource\RelationManagers;

use App\Enums\Gender;
use App\Models\Participant;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AthletesRelationManager extends RelationManager
{
    protected static string $relationship = 'athletes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('continent.membership.athletes');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make(static fn () => trans('participant.section.bio_heading'))
                    ->aside()
                    ->schema([
                        Components\TextInput::make('name')
                            ->label(fn () => trans('participant.field.name')),
                        Components\BelongsToSelect::make('class_id')
                            ->relationship('classification', 'label')
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->searchDebounce(500),
                        Components\Radio::make('gender')
                            ->options(Gender::toOptions()),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Columns\TextColumn::make('name')
                    ->label(fn () => trans('participant.field.name')),
                Columns\TextColumn::make('classification.label')
                    ->label(fn () => trans('participant.field.classification'))
                    ->width('14%')
                    ->alignCenter(),
                Columns\TextColumn::make('gender')
                    ->label(fn () => trans('participant.field.gender'))
                    ->formatStateUsing(fn (Participant $record) => $record->gender->label())
                    ->width('14%')
                    ->alignCenter(),
            ])
            ->filters([
                Filters\SelectFilter::make('classification')
                    ->label(fn () => trans('classification.singular'))
                    ->relationship('classification', 'label')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('gender')
                    ->label(fn () => trans('participant.field.gender'))
                    ->options(Gender::toOptions()),
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
