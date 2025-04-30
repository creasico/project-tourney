<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

trait Authenticated
{
    protected ?Authenticatable $user;

    public function authenticate()
    {
        $this->actingAs($this->user ??= User::factory()->create());

        return $this->user;
    }
}
