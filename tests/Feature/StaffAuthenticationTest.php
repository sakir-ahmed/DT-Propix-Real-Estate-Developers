<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Settings;
use Database\Seeders\CompanySeeder;

beforeEach(function () {
    $this->seed(CompanySeeder::class);
});

it('signs in with username, email, or mobile', function (string $login) {
    $this->post('/admin/login', [
        'login' => $login,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticated();
})->with([
    'username' => 'owner',
    'email' => 'owner@company.test',
    'mobile' => '01710000001',
]);

it('loads the owner from the session on the next request', function () {
    $this->post('/admin/login', [
        'login' => 'owner',
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    auth()->forgetGuards();

    $this->get('/admin')->assertOk();
});

it('rejects a password and throttles the sixth attempt', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/login', [
            'login' => 'owner',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    $this->postJson('/api/v1/auth/login', [
        'login' => 'owner',
        'password' => 'password',
    ])->assertStatus(429)
        ->assertJsonPath('error.code', 'TOO_MANY_REQUESTS');
});

it('logs out after the idle timeout', function () {
    $owner = User::query()->where('username', 'owner')->firstOrFail();

    $this->actingAs($owner)
        ->get('/admin')
        ->assertOk();

    $this->travel(31)->minutes();

    $this->get('/admin')
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});

it('logs out after the idle timeout stored in settings', function () {
    app(Settings::class)->set('security.idle_timeout_minutes', 5);
    $owner = User::query()->where('username', 'owner')->firstOrFail();
    $this->actingAs($owner)->get('/admin')->assertOk();

    $this->travel(6)->minutes();

    $this->get('/admin')->assertRedirect(route('admin.login'));
    $this->assertGuest();
});
