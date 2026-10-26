<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_post_id' => JobPost::factory(),
            'name' => fake()->name(),
            'mobile' => '88017'.fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'cover_letter' => fake()->paragraph(),
            'cv_path' => 'applications/'.fake()->uuid().'.pdf',
            'status' => 'received',
        ];
    }
}
