<?php

declare(strict_types=1);

use App\Actions\Access\LogAccessAction;
use App\Actions\Tenancy\CreateTenantAction;
use App\Models\AccessLog;
use App\Models\Branch;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\PlanSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('hides another tenant from queries and returns 404 for their branch url', function () {
    $this->seed(PlanSeeder::class);

    $action = app(CreateTenantAction::class);
    $first = $action->handle(tenantPayload('dhaka-heights', 'Dhaka Heights', '8801710000001', 'heights-owner'));
    $second = $action->handle(tenantPayload('gulshan-estates', 'Gulshan Estates', '8801810000002', 'gulshan-owner'));

    $firstBranch = Branch::query()->withoutGlobalScope('tenant')->where('tenant_id', $first->id)->firstOrFail();
    $secondBranch = Branch::query()->withoutGlobalScope('tenant')->where('tenant_id', $second->id)->firstOrFail();

    app(TenantContext::class)->set($first);
    expect(Branch::query()->pluck('id')->all())->toBe([$firstBranch->id])
        ->and(User::query()->count())->toBe(1);

    $owner = User::query()->where('username', 'heights-owner')->firstOrFail();

    $this->actingAs($owner)
        ->get(tenantUrl($second->slug, '/admin/branches/'.$secondBranch->id))
        ->assertNotFound();

    $this->actingAs($owner)
        ->get(tenantUrl($first->slug, '/admin/branches/'.$firstBranch->id))
        ->assertOk();

    auth()->forgetGuards();

    $this->post(tenantUrl($first->slug, '/admin/login'), [
        'login' => 'heights-owner',
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    auth()->forgetGuards();

    $this->get(tenantUrl($second->slug, '/admin/branches/'.$secondBranch->id))
        ->assertNotFound();
});

it('returns 404 for an unknown website host', function () {
    $this->get('http://missing.propix.test/')->assertNotFound();
});

it('shows the sign-in page at the platform root', function () {
    $this->get('http://127.0.0.1:8000/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Auth/Login'));
});

it('sends a signed-in super admin from the platform root to the tenant list', function () {
    $this->seed();

    $admin = User::query()->withoutGlobalScope('tenant')->where('is_super_admin', true)->firstOrFail();

    $this->actingAs($admin)
        ->get('http://127.0.0.1:8000/')
        ->assertRedirect(route('platform.tenants.index'));
});

it('writes an access log that another tenant cannot see', function () {
    $this->seed(PlanSeeder::class);
    $tenant = app(CreateTenantAction::class)->handle(tenantPayload('dhaka-heights', 'Dhaka Heights', '8801710000001', 'heights-owner'));
    $other = app(CreateTenantAction::class)->handle(tenantPayload('gulshan-estates', 'Gulshan Estates', '8801810000002', 'gulshan-owner'));

    app(TenantContext::class)->set($tenant);
    app(LogAccessAction::class)->handle('document.view', $tenant);

    expect(AccessLog::query()->where('action', 'document.view')->count())->toBe(1);

    app(TenantContext::class)->set($other);
    expect(AccessLog::query()->count())->toBe(0);
});

/**
 * @return array{
 *     name: string,
 *     slug: string,
 *     plan: string,
 *     message_credits: int,
 *     owner_name: string,
 *     owner_username: string,
 *     owner_email: string,
 *     owner_mobile: string,
 *     owner_password: string
 * }
 */
function tenantPayload(string $slug, string $name, string $mobile, string $username): array
{
    return [
        'name' => $name,
        'slug' => $slug,
        'plan' => 'starter',
        'message_credits' => 10,
        'owner_name' => $name.' Owner',
        'owner_username' => $username,
        'owner_email' => $username.'@example.test',
        'owner_mobile' => $mobile,
        'owner_password' => 'password',
    ];
}
