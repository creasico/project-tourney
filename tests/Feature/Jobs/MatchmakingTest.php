<?php

declare(strict_types=1);

use App\Jobs\Matchmaking;

test('class exists', function () {
    expect(class_exists(Matchmaking::class))->toBeTrue();
});
