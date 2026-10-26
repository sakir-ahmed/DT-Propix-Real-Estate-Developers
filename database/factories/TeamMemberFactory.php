<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'designation' => ['en' => fake()->jobTitle(), 'bn' => fake()->jobTitle()],
            'department' => fake()->randomElement(['leadership', 'sales', 'engineering', 'customer-care']),
            'bio' => ['en' => fake()->paragraph(2), 'bn' => fake()->paragraph(2)],
            'photo' => null,
            'email' => null,
            'phone' => null,
            'linkedin_url' => null,
            'is_published' => false,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['is_published' => true]);
    }
}
