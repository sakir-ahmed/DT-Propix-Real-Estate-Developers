<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaymentPlanTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentPlanTemplate>
 */
class PaymentPlanTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Booking 10, DP 15, installments 60, handover 15',
            'lines' => [
                ['label' => 'Booking money', 'kind' => 'booking_money', 'percent' => 10, 'due_days' => 0],
                ['label' => 'Down payment', 'kind' => 'down_payment', 'percent' => 15, 'due_days' => 30],
                ['label' => 'Installments', 'kind' => 'installment', 'percent' => 60, 'due_days' => 90],
                ['label' => 'Handover payment', 'kind' => 'handover', 'percent' => 15, 'due_days' => 1095],
            ],
        ];
    }
}
