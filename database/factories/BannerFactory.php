<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ['en' => fake()->sentence(5), 'bn' => fake()->sentence(5)],
            'subtitle' => ['en' => fake()->sentence(12), 'bn' => fake()->sentence(12)],
            'image' => null,
            'cta_label' => ['en' => 'View projects', 'bn' => 'View projects'],
            'cta_url' => '/en/projects',
            'is_published' => false,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['is_published' => true]);
    }
}
