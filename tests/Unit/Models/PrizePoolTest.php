<?php

declare(strict_types=1);

use App\Enums\MedalPrize;
use App\Models\DivisionMatch;
use App\Models\DivisionPrize;
use App\Models\PrizePool;

test('belongs to many prizes', function () {
    $model = PrizePool::factory()
        ->hasAttached(
            DivisionMatch::factory(),
            [
                'amount' => 100,
                'medal' => MedalPrize::Gold,
            ],
            'prizes'
        )
        ->createOne();

    expect($model->prizes)->toHaveCount(1);

    $prize = $model->prizes->first();

    expect($prize)->toBeInstanceOf(DivisionMatch::class);
    expect($prize->pool)->toBeInstanceOf(DivisionPrize::class);
    expect($prize->pool->medal)->toBe(MedalPrize::Gold);
});
