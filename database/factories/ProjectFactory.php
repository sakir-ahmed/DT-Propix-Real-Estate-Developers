<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'type' => fake()->randomElement(['apartment', 'commercial', 'plot', 'mixed']),
            'status' => fake()->randomElement(['ongoing', 'upcoming', 'completed']),
            'location' => fake()->city(),
            'launch_date' => now()->subMonths(6)->toDateString(),
            'handover_date' => now()->addYears(2)->toDateString(),
        ];
    }
}
