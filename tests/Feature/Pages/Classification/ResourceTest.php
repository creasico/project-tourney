<?php

declare(strict_types=1);

use App\Filament\Resources\ClassificationResource;

use function Pest\Laravel\get;

it('returns a successful response', function () {
    $page = get(ClassificationResource::getUrl('index'));

    $page->assertSuccessful();
});
