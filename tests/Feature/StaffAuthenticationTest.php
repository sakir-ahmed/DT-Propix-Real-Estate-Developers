<?php

declare(strict_types=1);

use App\Actions\Tenancy\CreateTenantAction;
use App\Models\User;
use Database\Seeders\PlanSeeder;

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

it('signs in with username, email, or mobile', function (string $login) {
    $this->post(tenantUrl('dhaka-heights', '/admin/login'), [
        'login' => $login,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticated();
})->with([
    'username' => 'heights-owner',
    'email' => 'owner@dhaka-heights.test',
    'mobile' => '01710000001',
]);

it('loads the owner from the session on the next request', function () {
    $this->post(tenantUrl('dhaka-heights', '/admin/login'), [
        'login' => 'heights-owner',
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    auth()->forgetGuards();

    $this->get(tenantUrl('dhaka-heights', '/admin'))->assertOk();
});

it('rejects a password and throttles the sixth attempt', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson(tenantUrl('dhaka-heights', '/api/v1/auth/login'), [
            'login' => 'heights-owner',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    $this->postJson(tenantUrl('dhaka-heights', '/api/v1/auth/login'), [
        'login' => 'heights-owner',
        'password' => 'password',
    ])->assertStatus(429)
        ->assertJsonPath('error.code', 'TOO_MANY_REQUESTS');
});

it('logs out after the idle timeout', function () {
    $owner = User::query()->withoutGlobalScope('tenant')->where('username', 'heights-owner')->firstOrFail();

    $this->actingAs($owner)
        ->get(tenantUrl('dhaka-heights', '/admin'))
        ->assertOk();

    $this->travel(31)->minutes();

    $this->get(tenantUrl('dhaka-heights', '/admin'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});
