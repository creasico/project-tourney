<?php

declare(strict_types=1);

use App\Filament\Resources\ClassificationResource\Pages\EditClassification;
use App\Models\Classification;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can edit a record', function () {
    $record = Classification::factory()->createOne();

    $page = livewire(EditClassification::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->fillForm([
        'label' => 'Updated',
    ])->call('save');

    $page->assertHasNoFormErrors();

    expect($record->refresh())
        ->label->toBe('Updated');
});

it('can delete a record', function () {
    $record = Classification::factory()->createOne();

    $page = livewire(EditClassification::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});
