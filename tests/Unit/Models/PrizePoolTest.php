<?php

declare(strict_types=1);

use App\Enums\MedalPrize;
use App\Models\Division;
use App\Models\DivisionPrize;
use App\Models\PrizePool;

it('belongs to many prizes', function () {
    $model = PrizePool::factory()
        ->hasAttached(
            Division::factory(),
            [
                'amount' => 100,
                'medal' => MedalPrize::Gold,
            ],
            'prizes'
        )
        ->createOne();

    expect($model->prizes)->toHaveCount(1);

    $prize = $model->prizes->first();

    expect($prize)->toBeInstanceOf(Division::class);
    expect($prize->pool)->toBeInstanceOf(DivisionPrize::class);
    expect($prize->pool->medal)->toBe(MedalPrize::Gold);
});
