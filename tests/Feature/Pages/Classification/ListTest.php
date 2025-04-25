<?php

declare(strict_types=1);

use App\Filament\Resources\ClassificationResource\Pages\ListClassifications;
use App\Models\Classification;

use function Pest\Livewire\livewire;

it('can show all records', function () {
    $records = Classification::factory()->createMany();

    $page = livewire(ListClassifications::class);

    $page->assertCanSeeTableRecords($records);
});
