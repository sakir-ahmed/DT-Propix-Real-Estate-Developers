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
            'is_published' => false,
            'show_prices' => true,
            'show_sold_units' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => true,
            'published_at' => now()->subDay(),
            'description_en' => fake()->paragraph(4),
            'description_bn' => fake()->paragraph(4),
            'tagline_en' => fake()->sentence(6),
            'tagline_bn' => fake()->sentence(6),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => ['is_featured' => true]);
    }
}
