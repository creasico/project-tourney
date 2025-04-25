<?php

declare(strict_types=1);

use App\Filament\Pages\Profile;
use App\Models\User;

use function Pest\Laravel\get;

it('can render the page', function () {
    $response = get(Profile::getUrl());

    $response->assertOk();
});

test('profile information can be updated', function () {
    // $user = User::factory()->create();

    // $this->actingAs($user);

    // doing something here

    $this->user->refresh();

    $this->assertSame('Test User', $this->user->name);
    $this->assertSame('test@example.com', $this->user->email);
    $this->assertNull($this->user->email_verified_at);
})->skip();

test('email verification status is unchanged when the email address is unchanged', function () {
    // $user = User::factory()->create();

    // $this->actingAs($user);

    // doing something here

    $this->assertNotNull($this->user->refresh()->email_verified_at);
})->skip();

test('user can delete their account', function () {
    // $user = User::factory()->create();

    // $this->actingAs($user);

    // doing something here

    $this->assertGuest();
    $this->assertNull($this->user->fresh());
})->skip();

test('correct password must be provided to delete account', function () {
    // $user = User::factory()->create();

    // $this->actingAs($user);

    // doing something here

    $this->assertNotNull($this->user->fresh());
})->skip();
