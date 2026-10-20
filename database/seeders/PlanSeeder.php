<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, array{name: string, monthly_price: string, limits: array<string, int|null>, modules: list<string>}> $plans */
        $plans = config('propix.plans');

        foreach ($plans as $slug => $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $plan['name'],
                    'monthly_price' => $plan['monthly_price'],
                    'limits' => $plan['limits'],
                    'modules' => $plan['modules'],
                    'is_active' => true,
                ],
            );
        }
    }
}
