<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\get;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // doing something here

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
})->skip();

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // doing something here

    $this->assertNotNull($user->refresh()->email_verified_at);
})->skip();

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // doing something here

    $this->assertGuest();
    $this->assertNull($user->fresh());
})->skip();

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // doing something here

    $this->assertNotNull($user->fresh());
})->skip();
