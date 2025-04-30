<?php

declare(strict_types=1);

use App\Filament\Resources\ContinentResource\Pages\ListContinents;
use App\Models\Continent;

use function Pest\Livewire\livewire;

it('can show all records', function () {
    $records = Continent::factory()->createMany();

    $page = livewire(ListContinents::class);

    $page->assertCanSeeTableRecords($records);
});
