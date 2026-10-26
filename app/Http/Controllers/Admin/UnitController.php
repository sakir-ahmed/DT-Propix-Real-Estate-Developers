<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\ChangeUnitStatusAction;
use App\Actions\Sales\CreateHoldAction;
use App\Enums\UnitStatus;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function hold(Request $request, Unit $unit): RedirectResponse
    {
        abort_unless($request->user()?->can('units.view'), 403);

        app(CreateHoldAction::class)->handle($unit, $request->user());

        return back()->with('success', __('units.held'));
    }

    public function release(Request $request, Unit $unit): RedirectResponse
    {
        abort_unless($request->user()?->canAny(['units.view', 'units.change-price']), 403);

        app(ChangeUnitStatusAction::class)->handle(
            $unit,
            UnitStatus::Available,
            $request->user(),
            'manual release',
        );

        return back()->with('success', __('units.released'));
    }
}
