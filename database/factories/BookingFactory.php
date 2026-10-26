<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_no' => 'BK-'.now()->format('y').'-'.fake()->unique()->numerify('######'),
            'branch_id' => null,
            'unit_id' => Unit::factory(),
            'customer_id' => Customer::factory(),
            'created_by' => User::factory(),
            'status' => 'confirmed',
            'price_snapshot' => ['lines' => [], 'total' => '0.00'],
            'discount_amount' => '0.00',
            'net_price' => '0.00',
        ];
    }
}
