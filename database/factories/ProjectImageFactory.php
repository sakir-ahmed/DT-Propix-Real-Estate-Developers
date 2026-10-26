<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectImage>
 */
class ProjectImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'kind' => 'gallery',
            'path' => '/images/website/placeholder-project.svg',
            'alt' => ['en' => fake()->sentence(4), 'bn' => fake()->sentence(4)],
            'caption' => null,
            'sort_order' => 0,
        ];
    }
}
