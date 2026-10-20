<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders every ui kit component for an authenticated admin', function () {
    $this->seed();

    $admin = User::query()->withoutGlobalScope('tenant')->where('is_super_admin', true)->firstOrFail();

    $this->actingAs($admin)
        ->get('/admin/ui-kit')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/UiKit')
            ->has('components', 40)
            ->where('components', fn ($components): bool => collect($components)->contains('AppIcon')
                && collect($components)->contains('DataTable')
                && collect($components)->contains('LanguageSwitch')
                && collect($components)->contains('ThemeToggle')));
});

it('switches the locale shared with inertia', function () {
    $this->seed();

    $admin = User::query()->withoutGlobalScope('tenant')->where('is_super_admin', true)->firstOrFail();

    $this->actingAs($admin)
        ->get('/locale/bn')
        ->assertRedirect();

    $this->get('/admin/ui-kit')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'bn')
            ->where('translations.nav.dashboard', 'ড্যাশবোর্ড'));
});
