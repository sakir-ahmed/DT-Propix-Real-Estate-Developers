<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => $this->mobile(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'locale' => 'en',
            'is_super_admin' => false,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => null,
            'branch_id' => null,
            'is_super_admin' => true,
            'username' => 'superadmin',
            'email' => 'platform@datatech.test',
            'mobile' => '8801700000000',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    private function mobile(): string
    {
        return '8801'.fake()->numberBetween(3, 9).fake()->unique()->numerify('########');
    }
}
