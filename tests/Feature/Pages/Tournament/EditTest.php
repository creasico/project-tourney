<?php

declare(strict_types=1);

use App\Events\TournamentPublished;
use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Models\Tournament;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Event;

use function Pest\Livewire\livewire;

it('can edit a record', function () {
    $record = Tournament::factory()
        ->unstarted()
        ->createOne();

    $page = livewire(EditTournament::class, [
        'record' => $record->getRouteKey(),
    ])->assertOk();

    $page->assertActionExists('publish');
});

it('cannot publish a record', function () {
    $record = Tournament::factory()
        ->unstarted()
        ->createOne();

    $page = livewire(EditTournament::class, [
        'record' => $record->getRouteKey(),
    ])->assertOk();

    Event::fake(TournamentPublished::class);

    $page->callAction('publish')
        ->assertHasNoActionErrors();

    Event::assertDispatched(TournamentPublished::class);
    Notification::assertNotified();
});

it('cannot delete a draft record', function () {
    $record = Tournament::factory()
        ->createOne();

    $page = livewire(EditTournament::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->assertActionDoesNotExist(DeleteAction::class);
});

it('can delete a pulblished record', function () {
    $record = Tournament::factory()
        ->published(started: false)
        ->createOne();

    $page = livewire(EditTournament::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->callAction(DeleteAction::class)
        ->assertHasNoActionErrors();

    $this->assertModelMissing($record);
});
