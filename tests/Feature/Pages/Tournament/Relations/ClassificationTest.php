<?php

declare(strict_types=1);

use App\Enums\MatchBye;
use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManagers\ClassesRelationManager;
use App\Models\Classification;
use App\Models\Tournament;
use Filament\Actions\EditAction;

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
    it('can update a record', function () {
        $class = Classification::factory()->createOne();
        $record = Tournament::factory()
            ->withClassifications(classifications: $class)
            ->withAthletes(count: 5, withClassification: $class)
            ->createOne();

        $page = livewire(ClassesRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => EditTournament::class,
        ])->assertOk();

        $page->callTableAction(EditAction::class, $record->classes->first(), [
            'bye' => MatchBye::Up,
            'division' => 5,
        ])->assertHasNoTableActionErrors();

        $record->refresh();

        expect($record->classes->first())->group->bye->toBe(MatchBye::Up);
    });
});
