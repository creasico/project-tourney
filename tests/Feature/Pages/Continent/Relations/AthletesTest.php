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
    it('can be filtered by :dataset', function ($key, $filter, $callback) {
        $record = Continent::factory()
            ->withAthletes(count: 5, withClassification: true)
            ->createOne();

        $page = livewire(AthletesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditContinent::class,
        ])->assertOk();

        $page->filterTable($key, $filter)
            ->assertCanSeeTableRecords($callback($record));
    })->with(collect([
        'gender' => [
            Gender::Female->value,
            fn (Continent $record) => $record->athletes()->onlyFemales()->get(),
        ],
        'classification.age_range' => [
            AgeRange::Early->value,
            fn (Continent $record) => $record->athletes()->hasAgeRange(AgeRange::Early)->get(),
        ],
    ])->mapWithKeys(function ($value, $key) {
        [$filter, $callback] = $value;

        return [
            $key => [$key, $filter, $callback],
        ];
    })->all());
});
