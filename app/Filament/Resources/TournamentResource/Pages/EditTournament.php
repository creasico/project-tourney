<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use App\Filament\Resources\TournamentResource\CreationWizardForm;
use App\Models\Tournament;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\HasWizard;

/**
 * @property \App\Models\Tournament $record
 */
class EditTournament extends EditRecord
{
    use CreationWizardForm {
        CreationWizardForm::form insteadof HasWizard;
        CreationWizardForm::getSteps insteadof HasWizard;
    }
    use HasWizard;

    protected static string $resource = TournamentResource::class;

    protected function getHeaderActions(): array
    {
        if ($this->record->is_draft) {
            return [];
        }

        return [
            Actions\DeleteAction::make()
                ->hidden(fn (Tournament $record) => $record->is_started),
        ];
    }

    protected function getFormActions(): array
    {
        if ($this->record->is_draft) {
            return [];
        }

        return [
            $this->getSaveFormAction()
                ->hidden(fn (Tournament $record) => $record->is_finished),

            $this->getCancelFormAction(),
        ];
    }

    public function getRelationManagers(): array
    {
        if ($this->record->is_draft) {
            return [];
        }

        return parent::getRelationManagers();
    }
}
