<?php

declare(strict_types=1);

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Filament\Resources\ContinentResource\Pages\EditContinent;
use App\Filament\Resources\ContinentResource\RelationManagers\AthletesRelationManager;
use App\Filament\Resources\ContinentResource\RelationManagers\ManagersRelationManager;
use App\Models\Classification;
use App\Models\Continent;
use Filament\Pages\Actions\DeleteAction;
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

it('can delete a record', function () {
    $record = Continent::factory()->createOne();

    $page = livewire(EditContinent::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
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

it('can filter athletes by gender', function () {
    $record = Continent::factory()
        ->withAthletes(5, fn () => [
            'class_id' => Classification::factory(),
        ])
        ->createOne();

    $page = livewire(AthletesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => EditContinent::class,
    ])->assertOk();

    $page->filterTable('gender', Gender::Female->value)
        ->assertCanSeeTableRecords($record->athletes()->onlyFemales()->get());
});

it('can filter athletes by age_range', function () {
    $record = Continent::factory()
        ->withAthletes(5, fn () => [
            'class_id' => Classification::factory(),
        ])
        ->createOne();

    $page = livewire(AthletesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => EditContinent::class,
    ])->assertOk();

    $page->filterTable('classification.age_range', AgeRange::Early->value)
        ->assertCanSeeTableRecords($record->athletes()->hasAgeRange(AgeRange::Early)->get());
});
