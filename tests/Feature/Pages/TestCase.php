<?php

declare(strict_types=1);

namespace Tests\Feature\Pages;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected ?Authenticatable $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->user ??= User::factory()->create());
    }
}
