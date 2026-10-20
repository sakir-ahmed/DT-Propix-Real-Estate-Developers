<?php

declare(strict_types=1);

use App\Actions\Tenancy\CreateTenantAction;
use Database\Seeders\PlanSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->tenant = app(CreateTenantAction::class)->handle([
        'name' => 'Dhaka Heights',
        'slug' => 'dhaka-heights',
        'plan' => 'starter',
        'message_credits' => 0,
        'owner_name' => 'Heights Owner',
        'owner_username' => 'heights-owner',
        'owner_email' => 'owner@dhaka-heights.test',
        'owner_mobile' => '8801710000001',
        'owner_password' => 'password',
    ]);
});

it('issues a staff token and returns the current user', function () {
    $response = $this->postJson(tenantUrl('dhaka-heights', '/api/v1/auth/login'), [
        'login' => 'heights-owner',
        'password' => 'password',
        'tenant_id' => 999,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.username', 'heights-owner');

    $token = $response->json('data.token');

    $this->withToken($token)
        ->getJson(tenantUrl('dhaka-heights', '/api/v1/auth/me'))
        ->assertOk()
        ->assertJsonPath('data.roles.0', 'owner');
});

it('does not authenticate a user on another tenant host', function () {
    app(CreateTenantAction::class)->handle([
        'name' => 'Gulshan Estates',
        'slug' => 'gulshan-estates',
        'plan' => 'starter',
        'message_credits' => 0,
        'owner_name' => 'Gulshan Owner',
        'owner_username' => 'gulshan-owner',
        'owner_email' => 'owner@gulshan-estates.test',
        'owner_mobile' => '8801810000002',
        'owner_password' => 'password',
    ]);

    $this->postJson(tenantUrl('dhaka-heights', '/api/v1/auth/login'), [
        'login' => 'gulshan-owner',
        'password' => 'password',
    ])->assertStatus(422);
});

it('sends and verifies an otp for the resolved tenant', function () {
    Http::fake([
        '*' => Http::response('Ok', 200),
    ]);

    $this->postJson(tenantUrl('dhaka-heights', '/api/v1/auth/otp/send'), [
        'mobile' => '01810000009',
    ])->assertOk()
        ->assertJsonPath('success', true);

    $this->postJson(tenantUrl('dhaka-heights', '/api/v1/auth/otp/verify'), [
        'mobile' => '01810000009',
        'code' => '000000',
    ])->assertStatus(422);
});

it('requires a token for the profile endpoint', function () {
    $this->getJson(tenantUrl('dhaka-heights', '/api/v1/auth/me'))
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHENTICATED');
});
