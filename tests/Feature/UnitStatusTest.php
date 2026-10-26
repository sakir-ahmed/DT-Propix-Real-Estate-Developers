<?php

declare(strict_types=1);

use App\Actions\Inventory\ChangeUnitStatusAction;
use App\Enums\UnitStatus;
use App\Models\Unit;
use App\Models\UnitStatusHistory;
use App\Models\User;
use Illuminate\Validation\ValidationException;

it('follows the legal path and writes history', function () {
    $user = User::factory()->create();
    $unit = Unit::factory()->create();

    $action = app(ChangeUnitStatusAction::class);

    $action->handle($unit, UnitStatus::OnHold, $user, 'hold');
    $action->handle($unit, UnitStatus::Booked, $user);
    $action->handle($unit, UnitStatus::Sold, $user, 'agreement');
    $action->handle($unit, UnitStatus::HandedOver, $user);

    expect($unit->fresh()->status)->toBe(UnitStatus::HandedOver)
        ->and(UnitStatusHistory::query()->where('unit_id', $unit->id)->count())->toBe(4);
});

it('rejects an illegal transition', function () {
    $unit = Unit::factory()->create();

    app(ChangeUnitStatusAction::class)->handle($unit, UnitStatus::Booked);

    expect(fn () => app(ChangeUnitStatusAction::class)->handle($unit->fresh(), UnitStatus::HandedOver))
        ->toThrow(ValidationException::class);

    expect($unit->fresh()->status)->toBe(UnitStatus::Booked);
});

it('allows cancelling a booking back to available', function () {
    $unit = Unit::factory()->create();

    app(ChangeUnitStatusAction::class)->handle($unit, UnitStatus::Booked);
    app(ChangeUnitStatusAction::class)->handle($unit->fresh(), UnitStatus::Available);

    expect($unit->fresh()->status)->toBe(UnitStatus::Available);
});
