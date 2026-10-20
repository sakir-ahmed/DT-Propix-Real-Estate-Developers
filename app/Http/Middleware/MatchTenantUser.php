<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchTenantUser
{
    public function __construct(private TenantContext $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $tenantId = $this->tenantContext->id();

        if ($tenantId === null) {
            abort_unless($user->is_super_admin, 404);

            return $next($request);
        }

        if ($user->is_super_admin || $user->tenant_id === $tenantId) {
            return $next($request);
        }

        abort(404);
    }
}
