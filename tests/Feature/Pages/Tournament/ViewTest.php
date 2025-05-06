<?php

declare(strict_types=1);

use App\Filament\Resources\TournamentResource\Pages\ViewTournament;
use App\Models\Tournament;

use function Pest\Livewire\livewire;

it('can view a record', function () {
    $record = Tournament::factory()->createOne();

    $page = livewire(ViewTournament::class, [
        'record' => $record->getRouteKey(),
    ])->assertOk();

    $page->assertActionExists('edit');
});
