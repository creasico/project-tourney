<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContinentResource\RelationManagers;

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Filament\Resources\ContinentResource\WithMembershipRecord;
use App\Models\Builders\PersonBuilder;
use App\Models\Person;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions;
use Filament\Tables\Filters;
use Filament\Tables\Grouping\Group;
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
            ->defaultGroup(
                Group::make('classification.age_range')
                    ->label(trans('classification.field.age_range'))
                    ->getTitleFromRecordUsing(fn (Person $record) => $record->classification->age_range->label())
            )
            ->columns($this->getMembershipTableColumns(true))
            ->filters([
                Filters\SelectFilter::make('gender')
                    ->label(trans('participant.field.gender'))
                    ->options(Gender::toOptions()),

                Filters\SelectFilter::make('classification.age_range')
                    ->label(trans('classification.field.age_range'))
                    ->options(AgeRange::toOptions())
                    ->query(function (PersonBuilder $query, $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->hasAgeRange($data['value']);
                    }),
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
