<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;

use function Pest\Livewire\livewire;

it('can edit record', function () {
    $record = User::factory()->createOne();

    $page = livewire(EditUser::class, [
        'record' => $record->getRouteKey(),
    ]);

    $page->assertFormSet([
        'name' => $record->name,
        'email' => $record->email,
    ]);
});
