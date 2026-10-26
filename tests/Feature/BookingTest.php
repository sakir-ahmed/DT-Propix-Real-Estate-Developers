<?php

declare(strict_types=1);

use App\Actions\Sales\CreateBookingAction;
use App\Models\Customer;
use App\Models\PaymentPlanTemplate;
use App\Models\ScheduleRow;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(CompanySeeder::class);

    $this->template = PaymentPlanTemplate::factory()->create([
        'lines' => [
            ['label' => 'Booking money', 'kind' => 'booking_money', 'percent' => 10, 'due_days' => 0],
            ['label' => 'Down payment', 'kind' => 'down_payment', 'percent' => 15, 'due_days' => 30],
            ['label' => 'Installments', 'kind' => 'installment', 'percent' => 60, 'due_days' => 365],
            ['label' => 'Handover', 'kind' => 'handover', 'percent' => 15, 'due_days' => 1095],
        ],
    ]);

    $this->unit = Unit::factory()->create([
        'size_sqft' => '1000.00',
        'base_rate' => '5000.00',
        'floor_premium' => '0.00',
        'parking_price' => '0.00',
        'utility_charges' => '0.00',
        'other_charges' => '0.00',
    ]);

    $this->customer = Customer::factory()->create();
    $this->owner = User::query()->where('username', 'owner')->firstOrFail();
});

it('books a unit with a price snapshot and a schedule that sums to the net price', function () {
    $booking = app(CreateBookingAction::class)->handle($this->unit, $this->customer, $this->owner, [
        'template_id' => $this->template->id,
    ]);

    expect($booking->booking_no)->toStartWith('BK-')
        ->and($booking->net_price)->toBe('5000000.00')
        ->and((float) $booking->scheduleRows->sum('amount'))->toBe(5000000.00)
        ->and($booking->unit->fresh()->status->value)->toBe('booked')
        ->and(ScheduleRow::query()->where('booking_id', $booking->id)->count())->toBe(4);
});

it('never changes the snapshot after later price edits', function () {
    $booking = app(CreateBookingAction::class)->handle($this->unit, $this->customer, $this->owner, [
        'template_id' => $this->template->id,
    ]);

    $this->unit->fresh()->update(['base_rate' => '9000.00']);

    expect($booking->fresh()->net_price)->toBe('5000000.00')
        ->and($booking->fresh()->price_snapshot['total'])->toBe('5000000.00');
});

it('prevents a second booking of the same unit', function () {
    app(CreateBookingAction::class)->handle($this->unit, $this->customer, $this->owner, [
        'template_id' => $this->template->id,
    ]);

    $otherCustomer = Customer::factory()->create();

    expect(fn () => app(CreateBookingAction::class)->handle($this->unit->fresh(), $otherCustomer, $this->owner, [
        'template_id' => $this->template->id,
    ]))->toThrow(ValidationException::class);

    expect($this->unit->fresh()->bookings()->count())->toBe(1);
});

it('rejects a schedule that does not equal the net price', function () {
    expect(fn () => app(CreateBookingAction::class)->handle($this->unit, $this->customer, $this->owner, [
        'manual_schedule' => [
            ['label' => 'Too little', 'kind' => 'booking_money', 'due_date' => now()->toDateString(), 'amount' => '100.00'],
        ],
    ]))->toThrow(ValidationException::class);
});

it('requires permission and a reason for a discount', function () {
    $executive = User::factory()->create();
    $executive->assignRole('sales-executive');

    expect(fn () => app(CreateBookingAction::class)->handle($this->unit, $this->customer, $executive, [
        'template_id' => $this->template->id,
        'discount_amount' => '50000.00',
        'discount_reason' => 'loyalty',
    ]))->toThrow(ValidationException::class);

    expect(fn () => app(CreateBookingAction::class)->handle($this->unit, $this->customer, $this->owner, [
        'template_id' => $this->template->id,
        'discount_amount' => '50000.00',
        'discount_reason' => '',
    ]))->toThrow(ValidationException::class);
});

it('caps the discount at the configured limit for managers', function () {
    $manager = User::factory()->create();
    $manager->assignRole('sales-manager');

    // 5% limit, this discount is 1%: allowed.
    $booking = app(CreateBookingAction::class)->handle($this->unit, $this->customer, $manager, [
        'template_id' => $this->template->id,
        'discount_amount' => '50000.00',
        'discount_reason' => 'early bird',
    ]);

    expect($booking->net_price)->toBe('4950000.00');

    $unit2 = Unit::factory()->create([
        'size_sqft' => '1000.00',
        'base_rate' => '5000.00',
        'floor_premium' => '0.00',
        'parking_price' => '0.00',
        'utility_charges' => '0.00',
        'other_charges' => '0.00',
    ]);

    // 20% discount is above the 5% limit.
    expect(fn () => app(CreateBookingAction::class)->handle($unit2, $this->customer, $manager, [
        'template_id' => $this->template->id,
        'discount_amount' => '1000000.00',
        'discount_reason' => 'big fish',
    ]))->toThrow(ValidationException::class);
});
