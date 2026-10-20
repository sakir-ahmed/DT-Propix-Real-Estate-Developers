<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('propix.platform.password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('PLATFORM_ADMIN_PASSWORD is required to seed the platform admin.');
        }

        User::query()->withoutGlobalScope('tenant')->updateOrCreate(
            [
                'is_super_admin' => true,
                'email' => config('propix.platform.email'),
            ],
            [
                'tenant_id' => null,
                'branch_id' => null,
                'name' => config('propix.platform.name'),
                'username' => config('propix.platform.username'),
                'mobile' => config('propix.platform.mobile'),
                'password' => $password,
                'locale' => 'en',
                'is_super_admin' => true,
            ],
        );
    }
}
