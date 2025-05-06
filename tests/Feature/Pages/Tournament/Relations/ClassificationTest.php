<?php

declare(strict_types=1);

use App\Enums\Category;
use App\Enums\MatchBye;
use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManagers\ClassesRelationManager;
use App\Jobs\CalculateMatchups;
use App\Models\Classification;
use App\Models\Tournament;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;

use function Pest\Livewire\livewire;

it('can have many classifications', function () {
    $record = Tournament::factory()
        ->withClassifications(count: 5)
        ->createOne();

    $page = livewire(ClassesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => EditTournament::class,
    ]);

    $page->assertOk()
        ->assertCanSeeTableRecords($record->classes);
});

describe('actions', function () {
    it('cannot update record on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ClassesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableActionHidden(EditAction::class);
    });

    it('can update record on started tournament', function () {
        $class = Classification::factory()->createOne();
        $record = Tournament::factory()
            ->unfinished()
            ->withClassifications(classifications: $class)
            ->withAthletes(count: 5, withClassification: $class)
            ->createOne();

        $page = livewire(ClassesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        Bus::fake(CalculateMatchups::class);

        $page->callTableAction(EditAction::class, $record->classes->first(), [
            'bye' => MatchBye::Up,
            'division' => 5,
        ])->assertHasNoTableActionErrors();

        Bus::assertDispatched(CalculateMatchups::class);
        Notification::assertNotified();

        $record->refresh();

        expect($record->classes->first())->group->bye->toBe(MatchBye::Up);
    });

    it('cannot regenerate all on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ClassesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableActionHidden('generate_all');
    });

    it('can regenerate all on started tournament', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withClassifications()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ClassesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        Bus::fake(CalculateMatchups::class);

        $page->callTableAction('generate_all', $record->classes->first())
            ->assertHasNoTableActionErrors();

        Bus::assertBatched(function (PendingBatch $batch) use ($record) {
            expect($batch->jobs)->toHaveCount(1);
            expect($batch->name)->toBe("Calculating matches for {$record->title}");

            return true;
        });

        Notification::assertNotified();
    });

    it('cannot edit classification on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withClassifications()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ClassesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableActionHidden(EditAction::class);
    });

    it('can edit classification on started tournament', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withClassifications()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ClassesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        Bus::fake(CalculateMatchups::class);

        $page->callTableAction(EditAction::class, $record->classes->first(), [
            'bye' => MatchBye::Up->value,
            'category' => Category::Match,
            'division' => 3,
        ])->assertHasNoTableActionErrors();

        Bus::assertDispatchedSync(CalculateMatchups::class);

        Notification::assertNotified();
    });

    it('cannot generate match on finished tournament', function () {
        $record = Tournament::factory()
            ->finished()
            ->withClassifications()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ClassesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->assertTableActionHidden('generate');
    });

    it('can generate match for classification', function () {
        $record = Tournament::factory()
            ->unfinished()
            ->withClassifications()
            ->withAthletes(count: 5)
            ->createOne();

        $page = livewire(ClassesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        Bus::fake(CalculateMatchups::class);

        $page->callTableAction('generate', $record->classes->first())
            ->assertHasNoTableActionErrors();

        Bus::assertDispatchedSync(CalculateMatchups::class);

        Notification::assertNotified();
    });
});
