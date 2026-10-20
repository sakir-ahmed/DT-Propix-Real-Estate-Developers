<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Inertia\Testing\AssertableInertia as Assert;

it('seeds plans, a platform admin, and two demo tenants', function () {
    $this->seed();

    expect(Plan::query()->count())->toBe(3)
        ->and(User::query()->withoutGlobalScope('tenant')->where('is_super_admin', true)->count())->toBe(1)
        ->and(Tenant::query()->count())->toBe(2)
        ->and(Branch::query()->withoutGlobalScope('tenant')->count())->toBe(2);
});

it('lets a super admin create a tenant with plan, trial, and credits', function () {
    $this->seed();

    $admin = User::query()->withoutGlobalScope('tenant')->where('is_super_admin', true)->firstOrFail();

    $this->actingAs($admin)
        ->post('/platform/tenants', [
            'name' => 'River View',
            'slug' => 'river-view',
            'plan' => 'growth',
            'trial_ends_at' => now()->addDays(14)->toDateString(),
            'message_credits' => 40,
            'owner_name' => 'River Owner',
            'owner_username' => 'river-owner',
            'owner_email' => 'owner@river-view.test',
            'owner_mobile' => '01910000004',
            'owner_password' => 'password',
        ])
        ->assertRedirect(route('platform.tenants.index'));

    $tenant = Tenant::query()->where('slug', 'river-view')->firstOrFail();

    expect($tenant->message_credits)->toBe(40)
        ->and($tenant->plan?->slug)->toBe('growth')
        ->and($tenant->trial_ends_at)->not->toBeNull();

    $this->actingAs($admin)
        ->get('/platform/tenants')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Platform/Tenants/Index'));
});

it('loads the super admin from the session on the next request', function () {
    $this->seed();

    $this->post('/admin/login', [
        'login' => 'superadmin',
        'password' => 'password',
    ])->assertRedirect(route('platform.tenants.index'));

    auth()->forgetGuards();

    $this->get('/platform/tenants')->assertOk();
});

it('exports a tenant without password hashes', function () {
    $this->seed();

    $exit = Artisan::call('tenants:export', ['tenant' => 'dhaka-heights']);

    expect($exit)->toBe(0);

    $path = trim(Artisan::output());
    $json = File::get($path.'/tenant.json');

    expect($json)->not->toContain('password')
        ->and($json)->toContain('Dhaka Heights');
});

it('schedules encrypted database backups', function () {
    $commands = collect(Schedule::events())->map(fn ($event): string => (string) $event->command);

    expect($commands->contains(fn (string $command): bool => str_contains($command, 'backup:run')))->toBeTrue()
        ->and($commands->contains(fn (string $command): bool => str_contains($command, 'backup:clean')))->toBeTrue();
});
