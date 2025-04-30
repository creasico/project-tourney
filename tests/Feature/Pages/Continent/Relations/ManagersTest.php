<?php

declare(strict_types=1);

use App\Filament\Resources\ContinentResource\Pages\EditContinent;
use App\Filament\Resources\ContinentResource\Pages\ListContinents;
use App\Filament\Resources\ContinentResource\RelationManagers\ManagersRelationManager;
use App\Models\Continent;
use Filament\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can have many managers', function () {
    $record = Continent::factory()
        ->withManagers(count: 5)
        ->createOne();

    $page = livewire(ManagersRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => ListContinents::class,
    ]);

    $page->assertOk()
        ->assertCanSeeTableRecords($record->managers);
});

describe('actions', function () {
    it('can update manager record', function () {
        $record = Continent::factory()
            ->withManagers(count: 5)
            ->createOne();

        $page = livewire(ManagersRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditContinent::class,
        ])->assertOk();

        $page->callTableAction(EditAction::class, $record->managers->first(), [
            'name' => 'Updated',
        ])->assertHasNoTableActionErrors();
    });
});
