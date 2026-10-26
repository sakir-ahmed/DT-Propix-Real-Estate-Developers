<?php

declare(strict_types=1);

namespace App\Actions\Setup;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SeedCompanyRolesAction
{
    public function handle(): void
    {
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
    }
}
