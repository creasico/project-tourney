<?php

declare(strict_types=1);

use App\Filament\Resources\ContinentResource\Pages\EditContinent;
use App\Filament\Resources\ContinentResource\RelationManagers\AthletesRelationManager;
use App\Filament\Resources\ContinentResource\RelationManagers\ManagersRelationManager;
use App\Models\Classification;
use App\Models\Continent;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can edit a record', function () {
    $record = Continent::factory()->createOne();

    $page = livewire(EditContinent::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->fillForm([
        'name' => 'Updated',
    ])->call('save');

    $page->assertHasNoFormErrors();

    expect($record->refresh())
        ->name->toBe('Updated');
});

it('can manage managers', function () {
    $record = Continent::factory()
        ->withManagers(5)
        ->createOne();

    $page = livewire(ManagersRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => EditContinent::class,
    ])->assertOk();

    $page->callTableAction(EditAction::class, $record->managers->first(), [
        'name' => 'Updated',
    ])->assertHasNoTableActionErrors();
});

it('can manage athletes', function () {
    $record = Continent::factory()
        ->withAthletes(5, fn () => [
            'class_id' => Classification::factory(),
        ])
        ->createOne();

    $page = livewire(AthletesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => EditContinent::class,
    ])->assertOk();

    $page->callTableAction(EditAction::class, $record->athletes->first(), [
        'name' => 'Updated',
    ])->assertHasNoTableActionErrors();
});
