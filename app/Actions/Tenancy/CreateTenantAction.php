<?php

declare(strict_types=1);

namespace App\Actions\Tenancy;

use App\Models\Branch;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PlanGate;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateTenantAction
{
    public function __construct(
        private PlanGate $planGate,
        private SeedTenantRolesAction $seedTenantRoles,
        private TenantContext $tenantContext,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     slug: string,
     *     plan: string,
     *     trial_ends_at?: string|null,
     *     limit_overrides?: array<string, int|null>|null,
     *     message_credits?: int,
     *     owner_name: string,
     *     owner_username: string,
     *     owner_email: string,
     *     owner_mobile: string,
     *     owner_password: string
     * }  $data
     */
    public function handle(array $data): Tenant
    {
        $previousId = $this->tenantContext->id();
        $bypassed = $this->tenantContext->isBypassed();

        try {
            return DB::transaction(function () use ($data): Tenant {
                $plan = Plan::query()->where('slug', $data['plan'])->firstOrFail();

                $tenant = Tenant::query()->create([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'plan_id' => $plan->id,
                    'status' => 'trial',
                    'trial_ends_at' => $data['trial_ends_at'] ?? now()->addDays(30),
                    'message_credits' => $data['message_credits'] ?? 0,
                    'limit_overrides' => $data['limit_overrides'] ?? null,
                    'locale' => 'en',
                    'timezone' => 'Asia/Dhaka',
                ]);

                $this->tenantContext->set($tenant);
                $this->planGate->assertWritable($tenant);

                $branch = Branch::query()->create([
                    'tenant_id' => $tenant->id,
                    'name' => 'Head office',
                    'code' => 'HO',
                    'is_head_office' => true,
                ]);

                $this->planGate->assertCanAddUser($tenant);

                $owner = User::query()->create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'name' => $data['owner_name'],
                    'username' => $data['owner_username'],
                    'email' => $data['owner_email'],
                    'mobile' => normalize_bd_mobile($data['owner_mobile']),
                    'password' => $data['owner_password'],
                    'locale' => 'en',
                    'is_super_admin' => false,
                ]);

                $this->seedTenantRoles->handle($tenant);
                $this->tenantContext->set($tenant);
                $owner->assignRole('owner');

                return $tenant->fresh(['plan', 'branches']) ?? $tenant;
            });
        } finally {
            $this->restore($previousId, $bypassed);
        }
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

        $this->tenantContext->set(Tenant::query()->find($previousId));
    }
}
