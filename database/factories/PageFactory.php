<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'slug' => str($title)->slug()->toString(),
            'title' => ['en' => $title, 'bn' => $title],
            'excerpt' => ['en' => fake()->sentence(12), 'bn' => fake()->sentence(12)],
            'body' => ['en' => '<p>'.fake()->paragraph(6).'</p>', 'bn' => '<p>'.fake()->paragraph(6).'</p>'],
            'template' => 'default',
            'meta_title' => null,
            'meta_description' => null,
            'is_published' => false,
            'published_at' => null,
            'show_in_footer' => false,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
    }
}
