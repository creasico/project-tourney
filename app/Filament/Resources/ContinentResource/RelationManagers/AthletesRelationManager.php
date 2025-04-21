<?php

namespace App\Filament\Resources\ContinentResource\RelationManagers;

use App\Enums\Gender;
use App\Filament\Resources\ContinentResource\WithMembershipRecord;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AthletesRelationManager extends RelationManager
{
    use WithMembershipRecord;

    protected static string $relationship = 'athletes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('continent.membership.athletes');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make(trans('participant.section.bio_heading'))
                    ->aside()
                    ->schema($this->getMembershipFormSchema(true)),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns($this->getMembershipTableColumns(true))
            ->filters([
                Filters\SelectFilter::make('age')
                    ->label(trans('classification.term.age'))
                    ->relationship('age', 'label')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('weight')
                    ->label(trans('classification.term.weight'))
                    ->relationship('weight', 'label')
                    ->searchable()
                    ->preload(),
                Filters\SelectFilter::make('gender')
                    ->label(trans('participant.field.gender'))
                    ->options(Gender::toOptions()),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions($this->getMembershipTableActions())
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
