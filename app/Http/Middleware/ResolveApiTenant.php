<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveApiTenant
{
    public function __construct(private TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        if ($user->is_super_admin && $user->tenant_id === null) {
            if ($this->tenantContext->id() !== null) {
                abort(404);
            }

            $this->tenantContext->bypass();

            return $next($request);
        }

        if ($user->tenant_id === null) {
            abort(404);
        }

        if ($this->tenantContext->id() !== null && $this->tenantContext->id() !== $user->tenant_id) {
            abort(404);
        }

        $tenant = Tenant::query()->find($user->tenant_id);

        if ($tenant === null) {
            abort(404);
        }

        $this->tenantContext->set($tenant);

        return $next($request);
    }
}
