<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('can render the page', function () {
    $response = get(route('filament.admin.auth.login'));

    $response->assertOk();
});
