<?php

declare(strict_types=1);

use App\Actions\Tenancy\CreateTenantAction;
use App\Actions\Tenancy\SeedTenantRolesAction;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\PlanSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('shows each role only its menu keys', function (string $role, array $keys) {
    $this->seed(PlanSeeder::class);
    $tenant = app(CreateTenantAction::class)->handle([
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

    app(TenantContext::class)->set($tenant);
    app(SeedTenantRolesAction::class)->handle($tenant);
    app(TenantContext::class)->set($tenant);

    $user = $role === 'owner'
        ? User::query()->where('username', 'heights-owner')->firstOrFail()
        : User::factory()->create([
            'tenant_id' => $tenant->id,
            'username' => $role.'-user',
            'email' => $role.'@dhaka-heights.test',
            'mobile' => '8801910000001',
        ]);

    if ($role !== 'owner') {
        $user->assignRole($role);
    }

    $this->actingAs($user)
        ->get(tenantUrl('dhaka-heights', '/admin'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('menu', function ($menu) use ($keys): bool {
                $actual = collect($menu)->pluck('key')->sort()->values()->all();
                $expected = collect($keys)->sort()->values()->all();

                return $actual === $expected;
            }));
})->with([
    'owner' => ['owner', ['dashboard', 'leads', 'units', 'bookings', 'collections', 'website', 'settings']],
    'general-manager' => ['general-manager', ['dashboard', 'leads', 'units', 'bookings', 'collections']],
    'sales-manager' => ['sales-manager', ['dashboard', 'leads', 'units', 'bookings']],
    'sales-executive' => ['sales-executive', ['leads', 'units', 'bookings']],
    'telecaller' => ['telecaller', ['leads']],
    'broker' => ['broker', ['leads']],
    'sales-accountant' => ['sales-accountant', ['units', 'bookings', 'collections']],
    'accountant' => ['accountant', ['dashboard', 'collections']],
    'project-manager' => ['project-manager', ['units']],
    'hr-manager' => ['hr-manager', []],
    'customer-support' => ['customer-support', []],
    'web-editor' => ['web-editor', ['website']],
]);
