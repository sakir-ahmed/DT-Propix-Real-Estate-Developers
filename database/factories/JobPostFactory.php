<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPost>
 */
class JobPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->jobTitle();

        return [
            'slug' => str($title.' '.fake()->unique()->numberBetween(1, 9999))->slug()->toString(),
            'title' => ['en' => $title, 'bn' => $title],
            'department' => fake()->randomElement(['sales', 'engineering', 'accounts', 'customer-care']),
            'location' => 'Dhaka',
            'employment_type' => 'full_time',
            'experience' => '2 to 4 years',
            'salary_range' => 'Negotiable',
            'vacancies' => 1,
            'summary' => ['en' => fake()->sentence(16), 'bn' => fake()->sentence(16)],
            'description' => ['en' => '<p>'.fake()->paragraph(5).'</p>', 'bn' => '<p>'.fake()->paragraph(5).'</p>'],
            'requirements' => [
                ['en' => fake()->sentence(8), 'bn' => fake()->sentence(8)],
                ['en' => fake()->sentence(8), 'bn' => fake()->sentence(8)],
            ],
            'benefits' => [
                ['en' => fake()->sentence(6), 'bn' => fake()->sentence(6)],
            ],
            'deadline' => now()->addDays(30)->toDateString(),
            'is_published' => false,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'deadline' => now()->subDay()->toDateString(),
        ]);
    }
}
