<?php

namespace App\Filament\Resources\TournamentResource;

use App\Filament\Resources\TournamentResource;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property null|\App\Models\Tournament $record
 */
trait CreationWizardForm
{
    public function form(Form $form): Form
    {
        if ($this->record?->is_published) {
            return parent::form($form);
        }

        return parent::form($form)
            ->schema([
                Components\Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false)
                    ->persistStepInQueryString(),
            ])
            ->columns(null);
    }

    protected function getSteps(): array
    {
        return [
            Components\Wizard\Step::make(trans('tournament.wizard.basic_label'))
                ->description(trans('tournament.wizard.basic_description'))
                ->columns(2)
                ->schema([
                    TournamentResource::getFormInfoSection(),
                    TournamentResource::getFormScheduleSection(),
                ])
                ->afterValidation(function (Get $get) {
                    $this->record = $this->getModel()::createAsDraft(
                        title: $get('title'),
                        level: $get('level'),
                        startDate: $get('start_date'),
                        finishDate: $get('finish_date'),
                        description: $get('description'),
                    );
                }),
            Components\Wizard\Step::make(trans('tournament.wizard.regulation_label'))
                ->description(trans('tournament.wizard.regulation_description'))
                ->schema([
                    Components\Section::make(trans('tournament.section.classification_heading'))
                        ->aside()
                        ->schema([
                            Components\Repeater::make('divisions')
                                ->relationship()
                                ->label(trans('tournament.field.divisions'))
                                ->columns(2)
                                ->schema([
                                    Components\Select::make('class_id')
                                        ->label(trans('tournament.field.class'))
                                        ->relationship(
                                            name: 'classification',
                                            titleAttribute: 'label',
                                            modifyQueryUsing: static fn (Builder $query) => $query->latest('order')
                                        )
                                        ->required(),
                                    Components\TextInput::make('division')
                                        ->label(trans('tournament.field.division'))
                                        ->numeric()
                                        ->required(),
                                ]),
                        ]),
                ])
                ->afterValidation(function (Get $get) {
                    foreach ($get('divisions') as $division) {
                        $this->record->divisions()->create($division);
                    }
                }),
            Components\Wizard\Step::make(trans('tournament.wizard.participation_label'))
                ->description(trans('tournament.wizard.participation_description'))
                ->schema([
                    // .
                ]),
        ];
    }
}
