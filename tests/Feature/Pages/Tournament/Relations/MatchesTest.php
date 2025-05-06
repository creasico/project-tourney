<?php

declare(strict_types=1);

use App\Enums\AgeRange;
use App\Enums\Gender;
use App\Events\MatchupFinished;
use App\Events\MatchupStarted;
use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManagers\MatchesRelationManager;
use App\Models\Tournament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Event;

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

describe('actions', function () {
    it('cannot start a record on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withMatches(count: 5)
            ->createOne();

        $page = livewire(MatchesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableActionHidden('start_match');
    });

    it('can start a record on started tournament', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withMatches(count: 5, state: [
                'started_at' => null,
                'finished_at' => null,
            ])
            ->createOne();

        $page = livewire(MatchesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        Event::fake(MatchupStarted::class);

        $page->callTableAction('start_match', $record->matches->first())
            ->assertHasNoTableActionErrors();

        Event::assertDispatched(MatchupStarted::class);
        Notification::assertNotified();
    });

    it('cannot choose_winner record on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withMatches(count: 5)
            ->createOne();

        $page = livewire(MatchesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableActionHidden('choose_winner');
    });

    it('can choose winner to on going match', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withMatches(count: 5, state: [
                'started_at' => now(),
                'finished_at' => null,
            ])
            ->createOne();

        $page = livewire(MatchesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        Event::fake(MatchupFinished::class);

        $page->callTableAction('choose_winner', $match = $record->matches->first(), [
            'athlete' => $match->athletes->first()->getKey(),
        ])->assertHasNoTableActionErrors();

        Event::assertDispatched(MatchupFinished::class);
        Notification::assertNotified();
    });

    it('can mark on going match as draw', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withMatches(count: 5, state: [
                'started_at' => now(),
                'finished_at' => null,
            ])
            ->createOne();

        $page = livewire(MatchesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        Event::fake(MatchupFinished::class);

        $page->callTableAction('set_as_draw', $record->matches->first())
            ->assertHasNoTableActionErrors();

        Event::assertDispatched(MatchupFinished::class);
        Notification::assertNotified();
    });
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
