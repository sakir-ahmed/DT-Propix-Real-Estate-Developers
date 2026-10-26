<?php

declare(strict_types=1);

use App\Actions\Inventory\PriceUnitAction;
use App\Models\Unit;

it('computes the breakdown from area, rate and charges', function () {
    $unit = Unit::factory()->create([
        'size_sqft' => '1250.00',
        'base_rate' => '8500.00',
        'floor_premium' => '50000.00',
        'parking_price' => '300000.00',
        'utility_charges' => '100000.00',
        'other_charges' => '25000.00',
    ]);

    $breakdown = app(PriceUnitAction::class)->handle($unit);

    $amounts = collect($breakdown['lines'])->pluck('amount', 'key');

    expect($amounts['base'])->toBe('10625000.00')
        ->and($amounts['floor_premium'])->toBe('50000.00')
        ->and($amounts['parking'])->toBe('300000.00')
        ->and($amounts['utility'])->toBe('100000.00')
        ->and($amounts['other'])->toBe('25000.00')
        ->and($breakdown['total'])->toBe('11100000.00');
});

it('handles a unit with no extras', function () {
    $unit = Unit::factory()->create([
        'size_sqft' => '1000.00',
        'base_rate' => '5000.00',
        'floor_premium' => '0.00',
        'parking_price' => '0.00',
        'utility_charges' => '0.00',
        'other_charges' => '0.00',
    ]);

    expect(app(PriceUnitAction::class)->handle($unit)['total'])->toBe('5000000.00');
});
