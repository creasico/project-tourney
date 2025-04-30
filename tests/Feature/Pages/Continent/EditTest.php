<?php

declare(strict_types=1);

use App\Filament\Resources\ContinentResource\Pages\EditContinent;
use App\Models\Continent;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can edit a record', function () {
    $record = Continent::factory()->createOne();

    $page = livewire(EditContinent::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->fillForm([
        'name' => 'Updated',
    ])->call('save');

    $page->assertHasNoFormErrors();

    expect($record->refresh())
        ->name->toBe('Updated');
});

it('can delete a record', function () {
    $record = Continent::factory()->createOne();

    $page = livewire(EditContinent::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});
