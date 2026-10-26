<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'slug' => str($title)->slug()->toString(),
            'title' => ['en' => ucfirst($title), 'bn' => ucfirst($title)],
            'summary' => ['en' => fake()->sentence(14), 'bn' => fake()->sentence(14)],
            'body' => ['en' => '<p>'.fake()->paragraph(5).'</p>', 'bn' => '<p>'.fake()->paragraph(5).'</p>'],
            'icon' => 'building',
            'features' => [
                ['en' => fake()->sentence(5), 'bn' => fake()->sentence(5)],
                ['en' => fake()->sentence(5), 'bn' => fake()->sentence(5)],
            ],
            'is_published' => false,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['is_published' => true]);
    }
}
