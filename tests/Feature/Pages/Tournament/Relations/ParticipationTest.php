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
    it('can be filtered by :dataset', function ($key, $filter, $callback) {
        $record = Tournament::factory()
            ->withClassifications()
            ->withMatches(count: 5)
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->filterTable($key, $filter)
            ->assertCanSeeTableRecords($callback($record));
    })->with(collect([
        'gender' => [
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

    it('can be filtered by "continent name"', function () {
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
});
