<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\UnitStatus;
use App\Models\Unit;
use App\Models\UnitStatusHistory;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Unit status is a state machine. Every change goes through this action and
 * writes a history row. Illegal transitions are rejected.
 */
class ChangeUnitStatusAction
{
    public function handle(Unit $unit, UnitStatus $to, ?User $user = null, ?string $note = null): Unit
    {
        $from = $unit->status;

        if ($from === $to) {
            return $unit;
        }

        if (! $from->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => __('units.illegal_transition', ['from' => $from->value, 'to' => $to->value]),
            ]);
        }

        $unit->status = $to;
        $unit->save();

        UnitStatusHistory::query()->create([
            'unit_id' => $unit->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'changed_by' => $user?->id,
            'note' => $note,
        ]);

        return $unit->refresh();
    }
}
