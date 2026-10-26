<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'slug' => str($title)->slug()->toString(),
            'title' => ['en' => $title, 'bn' => $title],
            'excerpt' => ['en' => fake()->sentence(18), 'bn' => fake()->sentence(18)],
            'body' => ['en' => '<p>'.fake()->paragraph(8).'</p>', 'bn' => '<p>'.fake()->paragraph(8).'</p>'],
            'category' => fake()->randomElement(Post::CATEGORIES),
            'tags' => ['real estate', 'dhaka'],
            'cover_image' => null,
            'author_id' => User::factory(),
            'reading_minutes' => fake()->numberBetween(3, 9),
            'meta_title' => null,
            'meta_description' => null,
            'is_published' => false,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => true,
            'published_at' => now()->subDays(fake()->numberBetween(1, 90)),
        ]);
    }
}
