<?php

declare(strict_types=1);

use App\Filament\Resources\PrizeResource;
use App\Models\PrizePool;

use function Pest\Laravel\get;

it('returns a successful response', function () {
    $page = get(PrizeResource::getUrl('index'));

    $page->assertOk();
});

it('can render create page', function () {
    $page = get(PrizeResource::getUrl('create'));

    $page->assertOk();
});

it('can render edit page', function () {
    $page = get(PrizeResource::getUrl('edit', [
        'record' => PrizePool::factory()->createOne(),
    ]));

    $page->assertOk();
});
