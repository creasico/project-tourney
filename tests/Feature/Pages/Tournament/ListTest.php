<?php

declare(strict_types=1);

use App\Filament\Resources\TournamentResource\Pages\ListTournaments;
use App\Models\Tournament;

use function Pest\Livewire\livewire;

it('can show all records', function () {
    $records = Tournament::factory()->createMany();

    $page = livewire(ListTournaments::class);

    $page->assertCanSeeTableRecords($records);
});
