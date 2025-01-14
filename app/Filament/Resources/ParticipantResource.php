<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Enums\ParticipantType;
use App\Filament\Resources\ParticipantResource\Pages;
use App\Models\Participant;
use App\View\Navigations\GroupManage;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ParticipantResource extends Resource
{
    use GroupManage;

    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('continent');
    }

    public static function form(Form $form): Form
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
                Components\Section::make(static fn () => trans('participant.section.info_heading'))
                    ->aside()
                    ->schema([
                        Components\Select::make('continent_id')
                            ->relationship('continent', 'name')
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->searchDebounce(500),
                        Components\Radio::make('type')
                            ->options(ParticipantType::toOptions()),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Columns\TextColumn::make('name')
                    ->label(fn () => trans('participant.field.name'))
                    ->description(fn (Participant $record) => $record->continent->name),
                Columns\TextColumn::make('classification.label')
                    ->label(fn () => trans('participant.field.classification'))
                    ->width('14%')
                    ->alignCenter(),
                Columns\TextColumn::make('type')
                    ->label(fn () => trans('participant.field.type'))
                    ->formatStateUsing(fn (Participant $record) => $record->type->label())
                    ->width('14%')
                    ->alignCenter(),
                Columns\TextColumn::make('gender')
                    ->label(fn () => trans('participant.field.gender'))
                    ->formatStateUsing(fn (Participant $record) => $record->gender->label())
                    ->width('14%')
                    ->alignCenter(),
            ])
            ->filters([
                Filters\SelectFilter::make('continent')
                    ->relationship('continent', 'name')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('classification')
                    ->relationship('classification', 'label')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('type')
                    ->options(ParticipantType::toOptions()),
                Filters\SelectFilter::make('gender')
                    ->options(Gender::toOptions()),
            ])
            ->actions([
                Actions\EditAction::make(),
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
            // .
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
        ];
    }
}
