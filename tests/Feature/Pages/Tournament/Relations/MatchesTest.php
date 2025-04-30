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
    it('can filter by gender', function () {
        $record = Tournament::factory()
            ->withClassifications()
            ->withMatches(count: 5)
            ->createOne();

        $page = livewire(MatchesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->filterTable('classification.gender', Gender::Female->value)
            ->assertCanSeeTableRecords(
                $record->participants()->onlyFemales()->get()
            );
    });

    it('can filter by age range', function () {
        $record = Tournament::factory()
            ->withClassifications()
            ->withMatches(count: 5)
            ->createOne();

        $page = livewire(MatchesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->filterTable('classification.age_range', AgeRange::Early->value)
            ->assertCanSeeTableRecords(
                $record->participants()->hasAgeRange(AgeRange::Early)->get()
            );
    });
});
