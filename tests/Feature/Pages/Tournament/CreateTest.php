<?php

declare(strict_types=1);

use App\Filament\Resources\TournamentResource\Pages\CreateTournament;
use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Models\Tournament;

use function Pest\Livewire\livewire;

it('can create a record', function () {
    $record = Tournament::factory()->makeOne();

    $page = livewire(CreateTournament::class);

    $page->fillForm([
        'title' => $record->title,
        'description' => $record->description,
        'level' => $record->level,
        'start_date' => $record->start_date,
        'finish_date' => $record->finish_date,
    ])->call('create');

    $model = Tournament::latest()->first();

    $page->assertHasNoFormErrors()
        ->assertRedirect(EditTournament::getUrl([
            'record' => $model->getRouteKey(),
        ]));
});
