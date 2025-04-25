<?php

declare(strict_types=1);

use App\Filament\Resources\PrizeResource\Pages\ListPrizes;
use App\Models\PrizePool;

use function Pest\Livewire\livewire;

it('can show all records', function () {
    $records = PrizePool::factory()->createMany();

    $page = livewire(ListPrizes::class);

    $page->assertCanSeeTableRecords($records);
});
