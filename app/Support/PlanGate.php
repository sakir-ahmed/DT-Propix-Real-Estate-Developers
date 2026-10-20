<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\ModuleDisabledException;
use App\Exceptions\PlanLimitReachedException;
use App\Exceptions\TenantNotWritableException;
use App\Models\Tenant;
use App\Models\User;

class PlanGate
{
    public function assertWritable(Tenant $tenant): void
    {
        if (! $tenant->status->isWritable()) {
            throw new TenantNotWritableException($tenant->status);
        }
    }

    public function assertModuleEnabled(Tenant $tenant, string $module): void
    {
        $this->assertWritable($tenant);

        $modules = $tenant->enabledModules();

        if (! in_array($module, $modules, true)) {
            throw new ModuleDisabledException($module);
        }
    }

    public function assertWithinLimit(Tenant $tenant, string $resource, int $currentCount, int $adding = 1): void
    {
        $this->assertWritable($tenant);

        $limit = $tenant->limitFor($resource);

        if ($limit === null) {
            return;
        }

        if (($currentCount + $adding) > $limit) {
            throw new PlanLimitReachedException($resource, $limit);
        }
    }

    public function assertCanAddUser(Tenant $tenant): void
    {
        $count = User::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_super_admin', false)
            ->count();

        $this->assertWithinLimit($tenant, 'users', $count);
    }
}
