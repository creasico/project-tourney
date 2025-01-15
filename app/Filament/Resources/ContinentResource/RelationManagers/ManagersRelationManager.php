<?php

namespace App\Filament\Resources\ContinentResource\RelationManagers;

use App\Filament\Resources\ContinentResource\WithMembershipRecord;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ManagersRelationManager extends RelationManager
{
    use WithMembershipRecord;

    protected static string $relationship = 'managers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('continent.membership.managers');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make(static fn () => trans('participant.section.bio_heading'))
                    ->aside()
                    ->schema($this->getMembershipFormSchema()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns($this->getMembershipTableColumns())
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
