<?php

declare(strict_types=1);

use App\Actions\Collections\RecordCollectionAction;
use App\Actions\Sales\CreateBookingAction;
use App\Models\Customer;
use App\Models\PaymentPlanTemplate;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingSummaryService;
use Database\Seeders\CompanySeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(CompanySeeder::class);

    $this->template = PaymentPlanTemplate::factory()->create([
        'lines' => [
            ['label' => 'Booking money', 'kind' => 'booking_money', 'percent' => 10, 'due_days' => -30],
            ['label' => 'Down payment', 'kind' => 'down_payment', 'percent' => 15, 'due_days' => 0],
            ['label' => 'Rest', 'kind' => 'installment', 'percent' => 75, 'due_days' => 365],
        ],
    ]);

    $this->unit = Unit::factory()->create([
        'size_sqft' => '1000.00',
        'base_rate' => '4000.00',
        'floor_premium' => '0.00',
        'parking_price' => '0.00',
        'utility_charges' => '0.00',
        'other_charges' => '0.00',
    ]);

    $this->customer = Customer::factory()->create();
    $this->owner = User::query()->where('username', 'owner')->firstOrFail();

    $this->booking = app(CreateBookingAction::class)->handle($this->unit, $this->customer, $this->owner, [
        'template_id' => $this->template->id,
    ]);
});

it('allocates to the earliest due rows and computes outstanding from allocations', function () {
    $result = app(RecordCollectionAction::class)->handle($this->booking, $this->owner, [
        'method' => 'cash',
        'amount' => '500000.00',
    ]);

    expect($result['receipt']->receipt_no)->toStartWith('MR-')
        ->and($result['receipt']->status)->toBe('posted')
        ->and($result['advance'])->toBe('0.00');

    $summary = app(BookingSummaryService::class)->totals($this->booking->fresh());

    // Booking money 400000 fully paid, down payment 600000 -> 100000 paid, rest due today 500000.
    expect($summary['collected'])->toBe('500000.00')
        ->and($summary['overdue'])->toBe('500000.00')
        ->and($summary['upcoming'])->toBe('3000000.00')
        ->and($summary['advance'])->toBe('0.00');
});

it('keeps an overpayment as an advance', function () {
    $result = app(RecordCollectionAction::class)->handle($this->booking, $this->owner, [
        'method' => 'bank',
        'amount' => '4020000.00',
    ]);

    expect($result['advance'])->toBe('20000.00');

    $summary = app(BookingSummaryService::class)->totals($this->booking->fresh());

    expect($summary['collected'])->toBe('4020000.00')
        ->and($summary['advance'])->toBe('20000.00')
        ->and($summary['overdue'])->toBe('0.00')
        ->and($summary['upcoming'])->toBe('0.00');
});

it('marks cheque receipts as provisional and excludes them from paid figures', function () {
    $result = app(RecordCollectionAction::class)->handle($this->booking, $this->owner, [
        'method' => 'cheque',
        'amount' => '400000.00',
        'reference' => 'CHQ-001',
    ]);

    expect($result['receipt']->status)->toBe('provisional');

    $summary = app(BookingSummaryService::class)->totals($this->booking->fresh());

    expect($summary['collected'])->toBe('0.00')
        ->and($summary['overdue'])->toBe('1000000.00');
});

it('rejects a non-positive amount', function () {
    expect(fn () => app(RecordCollectionAction::class)->handle($this->booking, $this->owner, [
        'method' => 'cash',
        'amount' => '0.00',
    ]))->toThrow(ValidationException::class);
});
