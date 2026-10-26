<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Actions\Inventory\ChangeUnitStatusAction;
use App\Enums\UnitStatus;
use App\Models\Hold;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reserves a unit for a configurable window. Nobody else can book it while
 * the hold is active; expiry releases it (ReleaseExpiredHolds command).
 */
class CreateHoldAction
{
    public function __construct(private ChangeUnitStatusAction $changeStatus) {}

    public function handle(Unit $unit, ?User $heldBy = null, ?string $expiresAt = null, string $tokenAmount = '0'): Hold
    {
        return DB::transaction(function () use ($unit, $heldBy, $expiresAt, $tokenAmount): Hold {
            $locked = Unit::query()->whereKey($unit->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== UnitStatus::Available) {
                throw ValidationException::withMessages([
                    'unit' => __('units.not_available', ['status' => $locked->status->value]),
                ]);
            }

            $this->changeStatus->handle($locked, UnitStatus::OnHold, $heldBy, 'hold');

            return Hold::query()->create([
                'unit_id' => $locked->id,
                'held_by' => $heldBy?->id,
                'token_amount' => $tokenAmount,
                'expires_at' => $expiresAt !== null && $expiresAt !== ''
                    ? now()->parse($expiresAt)
                    : now()->addHours((int) setting('sales.hold_hours', 48)),
            ]);
        });
    }
}
