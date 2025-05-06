<?php

declare(strict_types=1);

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManagers\ParticipantsRelationManager;
use App\Models\Continent;
use App\Models\Tournament;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

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

describe('actions', function () {
    it('can register athletes throught excel import', function () {
        $record = Tournament::factory()->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ]);

        // Excel::fake();

        $page->assertTableHeaderActionsExistInOrder(['import-athletes']);
    });

    it('cannot verify record on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withAthletes(count: 5, pivot: [
                'verified_at' => null,
            ])
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableActionHidden('verify');
    });

    it('can verify record on started tournament', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withAthletes(count: 5, pivot: [
                'verified_at' => null,
            ])
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->callTableAction('verify', $athlete = $record->participants->first())
            ->assertHasNoTableActionErrors();

        Notification::assertNotified();

        $record->fresh('unverifiedParticipants', 'verifiedParticipants');

        expect($record)
            ->verifiedParticipants->toHaveCount(1)
            ->unverifiedParticipants->toHaveCount(4);

        expect($record->verifiedParticipants->first()->getKey())->toBe($athlete->getKey());
    });

    it('cannot disqualify record on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableActionHidden('disqualify');
    });

    it('can disqualify record on started tournament', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->callTableAction('disqualify', $record->participants->first())
            ->assertHasNoTableActionErrors();

        Notification::assertNotified();
    });

    it('can deregister record when tournament is not started', function () {
        $record = Tournament::factory()
            ->published(started: false)
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->callTableAction('deregister', $record->participants->first())
            ->assertHasNoTableActionErrors();

        $record->refresh();

        expect($record)->participants->toHaveCount(4);
    });
});

describe('bulk actions', function () {
    it('cannot bulk verify record on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withClassifications()
            ->withAthletes(count: 5, pivot: [
                'verified_at' => null,
            ])
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableBulkActionHidden('bulk_verify');
    });

    it('can bulk verify record on started tournament', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withClassifications()
            ->withAthletes(count: 5, pivot: [
                'verified_at' => null,
            ])
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->callTableBulkAction('bulk_verify', $athlete = $record->participants->take(2))
            ->assertHasNoTableActionErrors();

        Notification::assertNotified();

        $record->fresh('unverifiedParticipants', 'verifiedParticipants');

        expect($record)
            ->verifiedParticipants->toHaveCount(2)
            ->unverifiedParticipants->toHaveCount(3);
    });

    it('cannot bulk disqualify record on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withClassifications()
            ->withAthletes(count: 5, pivot: [
                'verified_at' => null,
            ])
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableBulkActionHidden('bulk_disqualify');
    });

    it('can bulk disqualify record on started tournament', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withClassifications()
            ->withAthletes(count: 5, pivot: [
                'verified_at' => null,
            ])
            ->createOne();

        $page = livewire(ParticipantsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->callTableBulkAction('bulk_disqualify', $athlete = $record->participants->take(2))
            ->assertHasNoTableActionErrors();

        Notification::assertNotified();
    });
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
