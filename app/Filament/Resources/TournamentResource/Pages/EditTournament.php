<?php

declare(strict_types=1);

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use App\Models\Tournament;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

/**
 * @property \App\Models\Tournament $record
 */
class EditTournament extends EditRecord
{
    protected static string $resource = TournamentResource::class;

    protected function getHeaderActions(): array
    {
        if ($this->record->is_started) {
            return [];
        }

        return [
            Actions\Action::make('publish')
                ->hidden(fn (Tournament $record) => $record->is_published)
                ->requiresConfirmation()
                ->action(function (Tournament $record) {
                    $record->publish();

                    Notification::make()
                        ->success()
                        ->title(trans('tournament.notification.published_title'))
                        ->body(trans('tournament.notification.published_body', ['tournament' => $record->title]))
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->hidden(fn (Tournament $record) => $record->is_started),
        ];
    }

    protected function getFormActions(): array
    {
        if ($this->record->is_started) {
            return [];
        }

        return [
            $this->getSaveFormAction()
                ->hidden(fn (Tournament $record) => $record->is_finished),

            $this->getCancelFormAction(),
        ];
    }
}
