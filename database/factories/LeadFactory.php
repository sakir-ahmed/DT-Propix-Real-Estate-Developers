<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => null,
            'name' => fake()->name(),
            'mobile' => '8801'.fake()->numberBetween(3, 9).fake()->unique()->numerify('########'),
            'source' => fake()->randomElement(['website', 'phone', 'walk-in', 'broker', 'ads']),
            'stage' => 'new',
        ];
    }

    public function lost(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stage' => 'lost',
            'lost_reason' => 'budget',
        ]);
    }
}
