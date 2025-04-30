<?php

declare(strict_types=1);

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Filament\Resources\ContinentResource\Pages\EditContinent;
use App\Filament\Resources\ContinentResource\Pages\ListContinents;
use App\Filament\Resources\ContinentResource\RelationManagers\AthletesRelationManager;
use App\Models\Continent;
use Filament\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can have many athletes', function () {
    $record = Continent::factory()
        ->withAthletes(count: 5, withClassification: true)
        ->createOne();

    $page = livewire(AthletesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => ListContinents::class,
    ]);

    $page->assertOk()
        ->assertCanSeeTableRecords($record->athletes);
});

describe('actions', function () {
    it('can update athlete record', function () {
        $record = Continent::factory()
            ->withAthletes(count: 5, withClassification: true)
            ->createOne();

        $page = livewire(AthletesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditContinent::class,
        ])->assertOk();

        $page->callTableAction(EditAction::class, $record->athletes->first(), [
            'name' => 'Updated',
        ])->assertHasNoTableActionErrors();
    });
});

describe('filters', function () {
    it('can filter by gender', function () {
        $record = Continent::factory()
            ->withAthletes(count: 5, withClassification: true)
            ->createOne();

        $page = livewire(AthletesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditContinent::class,
        ])->assertOk();

        $page->filterTable('gender', Gender::Female->value)
            ->assertCanSeeTableRecords($record->athletes()->onlyFemales()->get());
    });

    it('can filter by age_range', function () {
        $record = Continent::factory()
            ->withAthletes(count: 5, withClassification: true)
            ->createOne();

        $page = livewire(AthletesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditContinent::class,
        ])->assertOk();

        $page->filterTable('classification.age_range', AgeRange::Early->value)
            ->assertCanSeeTableRecords($record->athletes()->hasAgeRange(AgeRange::Early)->get());
    });
});
