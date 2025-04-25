<?php

declare(strict_types=1);

use App\Enums\MedalPrize;
use App\Models\Division;
use App\Models\DivisionPrize;
use App\Models\PrizePool;

it('belongs to many prizes', function () {
    $model = Division::factory()
        ->hasAttached(
            PrizePool::factory(),
            [
                'amount' => 100,
                'medal' => MedalPrize::Gold,
            ],
            'prizes'
        )
        ->createOne();

    expect($model->prizes)->toHaveCount(1);

    $prize = $model->prizes->first();

    expect($prize)->toBeInstanceOf(PrizePool::class);
    expect($prize->pool)->toBeInstanceOf(DivisionPrize::class);
    expect($prize->pool->amount)->toBe(100);
    expect($prize->pool->medal)->toBe(MedalPrize::Gold);
});
