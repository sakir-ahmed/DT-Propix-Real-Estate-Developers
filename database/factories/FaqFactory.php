<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(Faq::CATEGORIES),
            'question' => ['en' => fake()->sentence(8).'?', 'bn' => fake()->sentence(8).'?'],
            'answer' => ['en' => fake()->paragraph(2), 'bn' => fake()->paragraph(2)],
            'is_published' => false,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['is_published' => true]);
    }
}
