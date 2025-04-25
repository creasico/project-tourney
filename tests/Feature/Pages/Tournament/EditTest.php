<?php

declare(strict_types=1);

use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Models\Tournament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can edit a record', function () {
    $record = Tournament::factory()->createOne();

    $page = livewire(EditTournament::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->assertOk();
});

it('cannot delete a draft record', function () {
    $record = Tournament::factory()->createOne();

    $page = livewire(EditTournament::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->assertActionDoesNotExist(DeleteAction::class);
});

it('can delete a pulblished record', function () {
    $record = Tournament::factory()->published(false)->createOne();

    $page = livewire(EditTournament::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});
