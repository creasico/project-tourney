<?php

declare(strict_types=1);

use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManagers\ClassesRelationManager;
use App\Models\Tournament;

use function Pest\Livewire\livewire;

it('can have many classifications', function () {
    $record = Tournament::factory()
        ->withClassifications(count: 5)
        ->createOne();

    $page = livewire(ClassesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => EditTournament::class,
    ]);

    $page->assertOk()
        ->assertCanSeeTableRecords($record->classes);
});
