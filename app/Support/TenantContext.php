<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\Context;

class TenantContext
{
    public const CONTEXT_KEY = 'tenant_id';

    private bool $bypassed = false;

    public function set(?Tenant $tenant): void
    {
        $this->bypassed = false;

        if ($tenant === null) {
            Context::forgetHidden(self::CONTEXT_KEY);

            return;
        }

        Context::addHidden(self::CONTEXT_KEY, $tenant->id);
    }

    /**
     * Platform-only escape hatch. Tenant-owned queries are fail-closed unless this is on.
     */
    public function bypass(): void
    {
        Context::forgetHidden(self::CONTEXT_KEY);
        $this->bypassed = true;
    }

    public function isBypassed(): bool
    {
        return $this->bypassed;
    }

    public function id(): ?int
    {
        $value = Context::getHidden(self::CONTEXT_KEY);

        return $value === null ? null : (int) $value;
    }

    public function tenant(): ?Tenant
    {
        $id = $this->id();

        if ($id === null) {
            return null;
        }

        return Tenant::query()->find($id);
    }

    public function requireTenant(): Tenant
    {
        $tenant = $this->tenant();

        abort_if($tenant === null, 404);

        return $tenant;
    }
}
