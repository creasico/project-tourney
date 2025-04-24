<?php

declare(strict_types=1);

use App\Jobs\GenerateMatches;

test('class exists', function () {
    expect(class_exists(GenerateMatches::class))->toBeTrue();
});
