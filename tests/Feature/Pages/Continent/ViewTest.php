<?php

declare(strict_types=1);

use App\Filament\Resources\ContinentResource\Pages\ViewContinent;
use App\Models\Continent;

use function Pest\Livewire\livewire;

it('can view a record', function () {
    $record = Continent::factory()->createOne();

    $page = livewire(ViewContinent::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->assertOk();
});
