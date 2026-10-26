<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'receipt_no' => 'MR-'.now()->format('y').'-'.fake()->unique()->numerify('######'),
            'booking_id' => Booking::factory(),
            'branch_id' => null,
            'customer_id' => Customer::factory(),
            'received_by' => User::factory(),
            'method' => 'cash',
            'reference' => null,
            'amount' => '1000.00',
            'received_at' => now()->toDateString(),
            'status' => 'posted',
        ];
    }
}
