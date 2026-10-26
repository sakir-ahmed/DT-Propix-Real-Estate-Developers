<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\CompanySeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('shows each role only its menu keys', function (string $role, array $keys) {
    $this->seed(CompanySeeder::class);

    $user = $role === 'owner'
        ? User::query()->where('username', 'owner')->firstOrFail()
        : User::factory()->create([
            'username' => $role.'-user',
            'email' => $role.'@company.test',
            'mobile' => '8801910000001',
        ]);

    if ($role !== 'owner') {
        $user->assignRole($role);
    }

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->where('menu', function ($menu) use ($keys): bool {
                $actual = collect($menu)->pluck('key')->sort()->values()->all();
                $expected = collect($keys)->sort()->values()->all();

                return $actual === $expected;
            }));
})->with([
    'owner' => ['owner', ['dashboard', 'leads', 'projects', 'customers', 'bookings', 'collections', 'settings']],
    'general-manager' => ['general-manager', ['dashboard', 'leads', 'projects', 'customers', 'bookings', 'collections']],
    'sales-manager' => ['sales-manager', ['dashboard', 'leads', 'projects', 'customers', 'bookings']],
    'sales-executive' => ['sales-executive', ['leads', 'projects', 'customers', 'bookings']],
    'telecaller' => ['telecaller', ['leads']],
    'broker' => ['broker', ['leads']],
    'sales-accountant' => ['sales-accountant', ['projects', 'customers', 'bookings', 'collections']],
    'accountant' => ['accountant', ['dashboard', 'collections']],
    'project-manager' => ['project-manager', ['projects']],
    'hr-manager' => ['hr-manager', []],
    'customer-support' => ['customer-support', ['customers']],
    'web-editor' => ['web-editor', ['projects']],
]);
