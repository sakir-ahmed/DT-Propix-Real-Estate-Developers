<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

use App\Models\Tenant;
use App\Support\TenantContext;
use Closure;

class RestoreTenantContext
{
    public function __construct(public ?int $tenantId) {}

    public function handle(object $job, Closure $next): void
    {
        $context = app(TenantContext::class);

        if ($this->tenantId === null) {
            $context->set(null);
            $next($job);

            return;
        }

        $tenant = Tenant::query()->find($this->tenantId);
        $context->set($tenant);

        try {
            $next($job);
        } finally {
            $context->set(null);
        }
    }
}
