<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Tenancy\CreateTenantAction;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoTenantSeeder extends Seeder
{
    public function run(CreateTenantAction $createTenant): void
    {
        $password = config('propix.demo.owner_password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('DEMO_OWNER_PASSWORD is required to seed demo tenants.');
        }

        $createTenant->handle([
            'name' => 'Dhaka Heights',
            'slug' => 'dhaka-heights',
            'plan' => 'starter',
            'trial_ends_at' => now()->addDays(30)->toDateString(),
            'message_credits' => 100,
            'owner_name' => 'Heights Owner',
            'owner_username' => 'heights.owner',
            'owner_email' => 'owner@dhaka-heights.test',
            'owner_mobile' => '8801710000001',
            'owner_password' => $password,
        ]);

        $createTenant->handle([
            'name' => 'Gulshan Estates',
            'slug' => 'gulshan-estates',
            'plan' => 'growth',
            'trial_ends_at' => now()->addDays(30)->toDateString(),
            'message_credits' => 250,
            'owner_name' => 'Gulshan Owner',
            'owner_username' => 'gulshan.owner',
            'owner_email' => 'owner@gulshan-estates.test',
            'owner_mobile' => '8801810000002',
            'owner_password' => $password,
        ]);
    }
}
