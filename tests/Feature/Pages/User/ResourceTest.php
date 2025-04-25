<?php

declare(strict_types=1);

use App\Filament\Resources\UserResource;
use App\Models\User;

use function Pest\Laravel\get;

it('returns a successful response', function () {
    $page = get(UserResource::getUrl('index'));

    $page->assertOk();
});

it('can render create page', function () {
    $page = get(UserResource::getUrl('create'));

    $page->assertOk();
});

it('can render edit page', function () {
    $page = get(UserResource::getUrl('edit', [
        'record' => User::factory()->createOne(),
    ]));

    $page->assertOk();
});
