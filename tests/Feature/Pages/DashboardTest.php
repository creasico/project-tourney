<?php

declare(strict_types=1);

use App\Filament\Pages\Dashboard;

use function Pest\Livewire\livewire;

it('can render the page', function () {
    $page = livewire(Dashboard::class);

    $page->assertOk();
});
