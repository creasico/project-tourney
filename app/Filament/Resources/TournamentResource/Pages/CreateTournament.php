<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use App\Filament\Resources\TournamentResource\CreationWizardForm;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

/**
 * @property null|\App\Models\Tournament $record
 */
class CreateTournament extends CreateRecord
{
    use CreationWizardForm {
        CreationWizardForm::form insteadof HasWizard;
        CreationWizardForm::getSteps insteadof HasWizard;
    }
    use HasWizard;

    protected static string $resource = TournamentResource::class;
}
