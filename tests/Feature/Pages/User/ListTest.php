<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;

use function Pest\Livewire\livewire;

it('can show all records', function () {
    $records = User::factory()->createMany();

    $page = livewire(ListUsers::class);

    $page->assertCanSeeTableRecords($records);
});
