<?php

use function Pest\Laravel\get;

it('returns a successful response', function () {
    $response = get('/');

    $response->assertRedirect(route('filament.admin.auth.login'));
});
