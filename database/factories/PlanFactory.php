<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Starter',
            'slug' => fake()->unique()->slug(2),
            'monthly_price' => '5000.00',
            'limits' => [
                'projects' => 3,
                'units' => 300,
                'users' => 5,
                'storage_mb' => 1024,
            ],
            'modules' => ['website', 'crm', 'units', 'booking', 'collections', 'reports'],
            'is_active' => true,
        ];
    }
}
