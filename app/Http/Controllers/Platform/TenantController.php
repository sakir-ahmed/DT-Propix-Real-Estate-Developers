<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Actions\Tenancy\CreateTenantAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreTenantRequest;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::query()
            ->with('plan')
            ->latest()
            ->get()
            ->map(fn (Tenant $tenant): array => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'status' => $tenant->status->value,
                'plan' => $tenant->plan?->name,
                'trial_ends_at' => $tenant->trial_ends_at?->toDateString(),
                'message_credits' => $tenant->message_credits,
            ]);

        return Inertia::render('Platform/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Platform/Tenants/Create', [
            'plans' => Plan::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(StoreTenantRequest $request, CreateTenantAction $createTenant): RedirectResponse
    {
        $overrides = $request->input('limit_overrides');
        $trialEndsAt = $request->input('trial_ends_at');

        $createTenant->handle([
            'name' => $request->string('name')->toString(),
            'slug' => $request->string('slug')->toString(),
            'plan' => $request->string('plan')->toString(),
            'trial_ends_at' => is_string($trialEndsAt) ? $trialEndsAt : null,
            'message_credits' => $request->integer('message_credits'),
            'limit_overrides' => is_array($overrides) ? $overrides : null,
            'owner_name' => $request->string('owner_name')->toString(),
            'owner_username' => $request->string('owner_username')->toString(),
            'owner_email' => $request->string('owner_email')->toString(),
            'owner_mobile' => $request->string('owner_mobile')->toString(),
            'owner_password' => $request->string('owner_password')->toString(),
        ]);

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', __('platform.tenant_created'));
    }
}
