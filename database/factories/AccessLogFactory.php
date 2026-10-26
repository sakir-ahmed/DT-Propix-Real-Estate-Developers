<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccessLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessLog>
 */
class AccessLogFactory extends Factory
{
    protected $model = AccessLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'action' => 'document.view',
            'subject_type' => null,
            'subject_id' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => 'Pest',
        ];
    }
}
