<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Actions\Inventory\ChangeUnitStatusAction;
use App\Enums\UnitStatus;
use App\Models\Hold;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

/**
 * Idempotent: running twice never releases a hold twice or errors.
 */
class ReleaseExpiredHoldsAction
{
    public function __construct(private ChangeUnitStatusAction $changeStatus) {}

    public function handle(): int
    {
        $released = 0;

        Hold::query()
            ->whereNull('released_at')
            ->where('expires_at', '<=', now())
            ->with('unit')
            ->chunkById(100, function ($holds) use (&$released): void {
                foreach ($holds as $hold) {
                    DB::transaction(function () use ($hold, &$released): void {
                        $fresh = Hold::query()->whereKey($hold->id)->lockForUpdate()->first();

                        if ($fresh === null || $fresh->released_at !== null) {
                            return;
                        }

                        $fresh->update(['released_at' => now()]);
                        $released++;

                        $unit = Unit::query()->whereKey($fresh->unit_id)->lockForUpdate()->first();

                        if ($unit !== null && $unit->status === UnitStatus::OnHold) {
                            $this->changeStatus->handle($unit, UnitStatus::Available, null, 'hold_expired');
                        }
                    });
                }
            });

        return $released;
    }
}
