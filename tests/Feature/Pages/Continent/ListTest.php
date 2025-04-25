<?php

declare(strict_types=1);

use App\Filament\Resources\ContinentResource\Pages\ListContinents;
use App\Filament\Resources\ContinentResource\RelationManagers\AthletesRelationManager;
use App\Filament\Resources\ContinentResource\RelationManagers\ManagersRelationManager;
use App\Models\Classification;
use App\Models\Continent;

use function Pest\Livewire\livewire;

it('can show all records', function () {
    $records = Continent::factory()->createMany();

    $page = livewire(ListContinents::class);

    $page->assertCanSeeTableRecords($records);
});

it('can have many managers', function () {
    $record = Continent::factory()
        ->withManagers(5)
        ->createOne();

    $page = livewire(ManagersRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => ListContinents::class,
    ]);

    $page->assertOk()
        ->assertCanSeeTableRecords($record->managers);
});

it('can have many athletes', function () {
    $record = Continent::factory()
        ->withAthletes(5, fn () => [
            'class_id' => Classification::factory(),
        ])
        ->createOne();

    $page = livewire(AthletesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => ListContinents::class,
    ]);

    $page->assertOk()
        ->assertCanSeeTableRecords($record->athletes);
});
