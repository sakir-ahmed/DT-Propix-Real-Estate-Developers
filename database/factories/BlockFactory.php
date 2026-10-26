<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Block;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Block>
 */
class BlockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => 'Block '.fake()->randomLetter(),
            'floors_count' => 10,
        ];
    }
}
