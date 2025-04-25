<?php

declare(strict_types=1);

use App\Filament\Resources\ContinentResource;

use function Pest\Laravel\get;

it('returns a successful response', function () {
    $page = get(ContinentResource::getUrl('index'));

    $page->assertOk();
});
