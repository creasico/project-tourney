<?php

declare(strict_types=1);

use App\Filament\Resources\TournamentResource;

use function Pest\Laravel\get;

it('returns a successful response', function () {
    $page = get(TournamentResource::getUrl('index'));

    $page->assertOk();
});
