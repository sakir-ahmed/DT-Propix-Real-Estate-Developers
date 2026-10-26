<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Inertia\Testing\AssertableInertia as Assert;

it('hides demo sign-in unless demo mode is enabled', function () {
    config(['propix.demo.enabled' => false]);

    $this->get('/admin/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('demoUsers', []));

    $this->post('/admin/login/demo', ['user_id' => 1])->assertNotFound();
});

it('refuses demo sign-in in production', function () {
    $this->app->detectEnvironment(fn (): string => 'production');
    config(['propix.demo.enabled' => true]);

    $this->get('/admin/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('demoUsers', []));

    $this->withoutMiddleware(PreventRequestForgery::class);

    $this->post('/admin/login/demo', ['user_id' => 1])->assertNotFound();
    $this->assertGuest();
});

it('lists users without secrets and signs in as the chosen user', function () {
    config(['propix.demo.enabled' => true]);
    $this->seed();

    $admin = User::query()->where('is_super_admin', true)->firstOrFail();
    $owner = User::query()->where('username', 'owner')->firstOrFail();

    $this->get('/admin/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('demoUsers', 2)
            ->where('demoUsers', function (mixed $users) use ($admin, $owner): bool {
                $rows = collect($users);

                return $rows->contains('id', $admin->id)
                    && $rows->contains('id', $owner->id)
                    && $rows->every(fn (array $user): bool => ! array_key_exists('password', $user) && ! array_key_exists('mobile', $user));
            }));

    $this->post('/admin/login/demo', ['user_id' => $admin->id])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

it('signs in the owner and opens their dashboard', function () {
    config(['propix.demo.enabled' => true]);
    $this->seed();

    $owner = User::query()->where('username', 'owner')->firstOrFail();

    $this->post('/admin/login/demo', ['user_id' => $owner->id])
        ->assertRedirect(route('admin.dashboard'));

    auth()->forgetGuards();

    $this->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('auth.user.username', 'owner'));
});
