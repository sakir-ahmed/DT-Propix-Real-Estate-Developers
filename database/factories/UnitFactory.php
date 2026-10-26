<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Block;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $seq = 0;

        return [
            'project_id' => Project::factory(),
            'block_id' => null,
            'number' => 'U-'.++$seq.'-'.fake()->randomNumber(3),
            'type' => 'apartment',
            'size_sqft' => '1250.00',
            'facing' => fake()->randomElement(['north', 'south', 'east', 'west']),
            'bedrooms' => 3,
            'bathrooms' => 2,
            'parking_count' => 1,
            'base_rate' => '8500.00',
            'floor_premium' => '50000.00',
            'parking_price' => '300000.00',
            'utility_charges' => '100000.00',
            'other_charges' => '0.00',
            'status' => 'available',
        ];
    }

    public function forProject(Project $project): static
    {
        return $this->state(fn (array $attributes): array => [
            'project_id' => $project->id,
            'block_id' => Block::factory()->for($project),
        ]);
    }
}
