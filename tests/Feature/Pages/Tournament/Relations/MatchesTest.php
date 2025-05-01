<?php

declare(strict_types=1);

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManagers\MatchesRelationManager;
use App\Models\Tournament;

use function Pest\Livewire\livewire;

it('can have many matches', function () {
    $record = Tournament::factory()
        ->withMatches(count: 5)
        ->createOne();

    $page = livewire(MatchesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => EditTournament::class,
    ])->assertOk();
});

describe('filters', function () {
    it('can be filtered by :dataset', function ($key, $filter, $callback) {
        $record = Tournament::factory()
            ->withClassifications()
            ->withMatches(count: 5)
            ->createOne();

        $page = livewire(MatchesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->filterTable($key, $filter)
            ->assertCanSeeTableRecords($callback($record));
    })->with(collect([
        'classification.gender' => [
            Gender::Female->value,
            fn (Tournament $record) => $record->participants()->onlyFemales()->get(),
        ],
        'classification.age_range' => [
            AgeRange::Early->value,
            fn (Tournament $record) => $record->participants()->hasAgeRange(AgeRange::Early)->get(),
        ],
    ])->mapWithKeys(function ($value, $key) {
        [$filter, $callback] = $value;

        return [
            $key => [$key, $filter, $callback],
        ];
    })->all());
});
