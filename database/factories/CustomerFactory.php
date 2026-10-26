<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $seq = 0;

        return [
            'branch_id' => null,
            'customer_code' => 'C-'.str_pad((string) (++$seq + 1000), 6, '0', STR_PAD_LEFT),
            'name' => fake()->name(),
            'mobile' => '8801'.fake()->numberBetween(3, 9).fake()->unique()->numerify('########'),
            'email' => fake()->safeEmail(),
            'nid' => null,
            'address' => fake()->address(),
        ];
    }
}
