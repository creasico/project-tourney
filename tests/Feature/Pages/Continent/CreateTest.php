<?php

declare(strict_types=1);

use App\Filament\Resources\ContinentResource\Pages\CreateContinent;
use App\Models\Continent;

use function Pest\Livewire\livewire;

it('can create a record', function () {
    $record = Continent::factory()->make();

    $page = livewire(CreateContinent::class);

    $page->fillForm([
        'name' => $record->name,
    ])->call('create');

    $page->assertHasNoFormErrors();
});
