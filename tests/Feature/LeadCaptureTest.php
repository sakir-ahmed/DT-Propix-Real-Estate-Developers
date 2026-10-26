<?php

declare(strict_types=1);

use App\Actions\Crm\CaptureLeadAction;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;

it('creates one lead and merges a repeat enquiry by mobile', function () {
    $user = User::factory()->create();
    $action = app(CaptureLeadAction::class);

    $first = $action->handle(['name' => 'Rafiq', 'mobile' => '01711000001', 'source' => 'website'], $user);
    $second = $action->handle(['name' => 'Rafiq Uddin', 'mobile' => '+8801711000001', 'source' => 'phone', 'note' => 'Called again'], $user);

    expect($first['created'])->toBeTrue()
        ->and($second['created'])->toBeFalse()
        ->and($second['lead']->id)->toBe($first['lead']->id)
        ->and(Lead::query()->count())->toBe(1)
        ->and(LeadActivity::query()->where('lead_id', $first['lead']->id)->count())->toBe(2);
});

it('normalizes the mobile before matching', function () {
    $user = User::factory()->create();

    $result = app(CaptureLeadAction::class)->handle(['name' => 'Nusrat', 'mobile' => '01811000002'], $user);

    expect($result['lead']->mobile)->toBe('8801811000002');
});

it('creates a lead without a user in a console context', function () {
    $result = app(CaptureLeadAction::class)->handle(['name' => 'Walk-in', 'mobile' => '01911000003']);

    expect($result['created'])->toBeTrue()
        ->and($result['lead']->assignee_id)->toBeNull();
});
