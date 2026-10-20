<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\HostTenantResolver;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveWebsite
{
    public function __construct(
        private HostTenantResolver $hosts,
        private TenantContext $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if ($this->hosts->isPlatformHost($host)) {
            $this->tenantContext->set(null);

            return $next($request);
        }

        $tenant = $this->tenantContext->tenant() ?? $this->hosts->find($host);

        if ($tenant === null) {
            abort(404);
        }

        $this->tenantContext->set($tenant);

        return $next($request);
    }
}
