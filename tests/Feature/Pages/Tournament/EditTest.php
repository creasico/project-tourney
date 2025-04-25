<?php

declare(strict_types=1);

use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Models\Tournament;

use function Pest\Livewire\livewire;

it('can edit a record', function () {
    $record = Tournament::factory()->createOne();

    $page = livewire(EditTournament::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->assertOk();
});
