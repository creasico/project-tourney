<?php

declare(strict_types=1);

use App\Filament\Pages\Settings;

use function Pest\Laravel\get;

it('can render the page', function () {
    $response = get(Settings::getUrl());

    $response->assertOk();
});
