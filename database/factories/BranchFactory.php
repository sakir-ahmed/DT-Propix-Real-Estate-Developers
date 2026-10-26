<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Head office',
            'code' => fake()->unique()->bothify('BR-###'),
            'address' => fake()->address(),
            'is_head_office' => true,
        ];
    }

    public function satellite(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => fake()->city().' branch',
            'is_head_office' => false,
        ]);
    }
}
