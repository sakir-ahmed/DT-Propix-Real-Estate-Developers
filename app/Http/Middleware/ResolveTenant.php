<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use App\Support\DemoMode;
use App\Support\HostTenantResolver;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        private HostTenantResolver $hosts,
        private TenantContext $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if ($this->hosts->isPlatformHost($host)) {
            $this->tenantContext->set($this->demoTenant($request));

            return $next($request);
        }

        $tenant = $this->hosts->find($host);

        if ($tenant === null) {
            abort(404);
        }

        $this->tenantContext->set($tenant);

        $user = $request->user();

        if ($user instanceof User && ! $user->is_super_admin && $user->tenant_id !== $tenant->id) {
            abort(404);
        }

        return $next($request);
    }

    private function demoTenant(Request $request): ?Tenant
    {
        if (! app(DemoMode::class)->enabled() || ! $request->hasSession()) {
            return null;
        }

        $userId = $request->session()->get(Auth::guard('web')->getName());

        if (! is_numeric($userId)) {
            return null;
        }

        $user = User::query()->withoutGlobalScopes()->find((int) $userId);

        if (! $user instanceof User || $user->is_super_admin || $user->tenant_id === null) {
            return null;
        }

        return Tenant::query()->find($user->tenant_id);
    }
}
