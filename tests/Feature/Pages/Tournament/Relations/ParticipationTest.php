<?php

declare(strict_types=1);

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManagers\ParticipantsRelationManager;
use App\Models\Continent;
use App\Models\Tournament;

use function Pest\Livewire\livewire;

it('can have many participants', function () {
    $record = Tournament::factory()
        ->withAthletes(count: 5)
        ->createOne();

    $page = livewire(ParticipantsRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => EditTournament::class,
    ]);

    $page->assertOk()
        ->assertCanSeeTableRecords($record->participants);
});

describe('filters', function () {
    it('can filter by continent name', function () {
        $continents = Continent::factory(2)->createMany();
        $record = Tournament::factory()
            ->withAthletes(
                withContinent: fn () => fake()->randomElement($continents),
                count: 5,
            )
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->filterTable('continent.name', $continentId = $continents->first()->getKey())
            ->assertCanSeeTableRecords(
                $record->participants()->where('continent_id', $continentId)->get()
            );
    });

    it('can filter by gender', function () {
        $record = Tournament::factory()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->filterTable('gender', Gender::Female->value)
            ->assertCanSeeTableRecords(
                $record->participants()->onlyFemales()->get()
            );
    });

    it('can filter by age_range', function () {
        $record = Tournament::factory()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->filterTable('classification.age_range', AgeRange::Early->value)
            ->assertCanSeeTableRecords(
                $record->participants()->hasAgeRange(AgeRange::Early)->get()
            );
    });
});
