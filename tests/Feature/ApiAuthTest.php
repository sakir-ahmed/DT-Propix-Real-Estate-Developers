<?php

declare(strict_types=1);

use Database\Seeders\CompanySeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(CompanySeeder::class);
});

it('issues a staff token and returns the current user', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'login' => 'owner',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.username', 'owner');

    $token = $response->json('data.token');

    $this->withToken($token)
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.roles.0', 'owner');
});

it('sends and verifies an otp', function () {
    Http::fake([
        '*' => Http::response('Ok', 200),
    ]);

    $this->postJson('/api/v1/auth/otp/send', [
        'mobile' => '01810000009',
    ])->assertOk()
        ->assertJsonPath('success', true);

    $this->postJson('/api/v1/auth/otp/verify', [
        'mobile' => '01810000009',
        'code' => '000000',
    ])->assertStatus(422);
});

it('requires a token for the profile endpoint', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});
