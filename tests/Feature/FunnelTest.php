<?php

declare(strict_types=1);

use App\Actions\Collections\RecordCollectionAction;
use App\Actions\Crm\CaptureLeadAction;
use App\Actions\Sales\CreateBookingAction;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\PaymentPlanTemplate;
use App\Models\SiteVisit;
use App\Models\Unit;
use App\Models\User;
use App\Services\FunnelService;
use Database\Seeders\CompanySeeder;

it('counts the funnel with the fixed definitions', function () {
    $this->seed(CompanySeeder::class);
    $owner = User::query()->where('username', 'owner')->firstOrFail();
    $action = app(CaptureLeadAction::class);

    $action->handle(['name' => 'A', 'mobile' => '01711000001'], $owner);
    $action->handle(['name' => 'B', 'mobile' => '01811000002'], $owner);
    $action->handle(['name' => 'B again', 'mobile' => '01811000002'], $owner);

    $lead = Lead::query()->where('mobile', '8801811000002')->firstOrFail();
    SiteVisit::factory()->completed()->for($lead)->create();

    $template = PaymentPlanTemplate::factory()->create();
    $unit = Unit::factory()->create([
        'size_sqft' => '1000.00',
        'base_rate' => '5000.00',
        'floor_premium' => '0.00',
        'parking_price' => '0.00',
        'utility_charges' => '0.00',
        'other_charges' => '0.00',
    ]);
    $customer = Customer::factory()->create();

    $booking = app(CreateBookingAction::class)->handle($unit, $customer, $owner, [
        'template_id' => $template->id,
    ]);
    app(RecordCollectionAction::class)->handle($booking, $owner, ['method' => 'cash', 'amount' => '250000.00']);

    $funnel = app(FunnelService::class)->summary();

    expect($funnel['leads'])->toBe(2)
        ->and($funnel['site_visits'])->toBe(1)
        ->and($funnel['bookings'])->toBe(1)
        ->and($funnel['sales'])->toBe('0.00')
        ->and($funnel['collection'])->toBe('250000.00');
});

it('counts a sale only when the agreement is signed', function () {
    $this->seed(CompanySeeder::class);
    $owner = User::query()->where('username', 'owner')->firstOrFail();

    $template = PaymentPlanTemplate::factory()->create();
    $unit = Unit::factory()->create([
        'size_sqft' => '1000.00',
        'base_rate' => '5000.00',
        'floor_premium' => '0.00',
        'parking_price' => '0.00',
        'utility_charges' => '0.00',
        'other_charges' => '0.00',
    ]);
    $customer = Customer::factory()->create();

    $booking = app(CreateBookingAction::class)->handle($unit, $customer, $owner, [
        'template_id' => $template->id,
    ]);

    $funnel = app(FunnelService::class)->summary();
    expect($funnel['sales'])->toBe('0.00');

    $booking->update(['agreement_signed_at' => now()]);

    $funnel = app(FunnelService::class)->summary();
    expect($funnel['sales'])->toBe('5000000.00');
});
