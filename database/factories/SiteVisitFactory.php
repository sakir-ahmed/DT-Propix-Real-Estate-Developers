<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\SiteVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteVisit>
 */
class SiteVisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'scheduled_at' => now()->addDays(2),
            'completed_at' => null,
            'cancelled_at' => null,
            'vehicle' => null,
            'feedback' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'scheduled_at' => now()->subDays(2),
            'completed_at' => now()->subDays(2)->addHours(2),
        ]);
    }
}
