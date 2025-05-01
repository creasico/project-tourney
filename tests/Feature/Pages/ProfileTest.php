<?php

declare(strict_types=1);

use App\Filament\Pages\Profile;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('can render the page', function () {
    $response = get(Profile::getUrl());

    $response->assertOk();
});

test('profile information can be updated', function () {
    $page = livewire(Profile::class)->fillForm([
        'name' => 'Updated',
        'email' => 'test@example.com',
    ])->call('save');

    $page->assertHasNoFormErrors();

    $this->user->refresh();

    $this->assertSame('Updated', $this->user->name);
    $this->assertSame('test@example.com', $this->user->email);
});
