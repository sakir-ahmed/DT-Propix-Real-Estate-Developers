<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TenantStatus;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(2),
            'plan_id' => Plan::factory(),
            'status' => TenantStatus::Trial,
            'trial_ends_at' => now()->addDays(30),
            'message_credits' => 0,
            'locale' => 'en',
            'timezone' => 'Asia/Dhaka',
        ];
    }

    public function readOnly(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TenantStatus::ReadOnly,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TenantStatus::Suspended,
            'suspended_at' => now(),
        ]);
    }
}
