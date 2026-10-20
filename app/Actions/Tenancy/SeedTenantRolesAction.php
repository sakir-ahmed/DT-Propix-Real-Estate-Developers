<?php

declare(strict_types=1);

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use App\Support\TenantContext;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SeedTenantRolesAction
{
    public function __construct(private TenantContext $tenantContext) {}

    public function handle(Tenant $tenant): void
    {
        $previousId = $this->tenantContext->id();
        $bypassed = $this->tenantContext->isBypassed();

        $this->tenantContext->set($tenant);

        /** @var list<string> $permissionNames */
        $permissionNames = config('propix.permissions');

        foreach ($permissionNames as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        /** @var list<string> $roles */
        $roles = config('propix.roles');

        foreach ($roles as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            /** @var list<string> $granted */
            $granted = config('propix.role_permissions.'.$roleName, []);

            if (in_array('*', $granted, true)) {
                $role->syncPermissions(Permission::query()->where('guard_name', 'web')->get());
            } else {
                $role->syncPermissions($granted);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->restore($previousId, $bypassed);
    }

    private function restore(?int $previousId, bool $bypassed): void
    {
        if ($bypassed) {
            $this->tenantContext->bypass();

            return;
        }

        if ($previousId === null) {
            $this->tenantContext->set(null);

            return;
        }

        $previous = Tenant::query()->find($previousId);
        $this->tenantContext->set($previous);
    }
}
