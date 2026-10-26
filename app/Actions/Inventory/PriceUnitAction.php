<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Models\Unit;

/**
 * Computes the full price breakdown for a unit. One source of truth for pricing;
 * bookings store the result as an immutable snapshot.
 */
class PriceUnitAction
{
    /**
     * @return array{
     *     lines: list<array{key: string, label: string, amount: string}>,
     *     total: string
     * }
     */
    public function handle(Unit $unit): array
    {
        $lines = [
            ['key' => 'base', 'label' => __('ui.price_base', ['size' => $unit->size_sqft, 'rate' => $unit->base_rate]), 'amount' => $this->money((float) $unit->size_sqft * (float) $unit->base_rate)],
            ['key' => 'floor_premium', 'label' => __('ui.price_floor_premium'), 'amount' => $this->money((float) $unit->floor_premium)],
            ['key' => 'parking', 'label' => __('ui.price_parking'), 'amount' => $this->money((float) $unit->parking_price)],
            ['key' => 'utility', 'label' => __('ui.price_utility'), 'amount' => $this->money((float) $unit->utility_charges)],
            ['key' => 'other', 'label' => __('ui.price_other'), 'amount' => $this->money((float) $unit->other_charges)],
        ];

        $total = array_sum(array_map(fn (array $line): float => (float) $line['amount'], $lines));

        return [
            'lines' => $lines,
            'total' => $this->money($total),
        ];
    }

    private function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
