<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\PriceUnitAction;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @property-read int $units_count
 * @property-read int $available_units_count
 */
class ProjectController extends Controller
{
    public function index(): Response
    {
        /** @var Collection<int, Project> $all */
        $all = Project::query()
            ->withCount([
                'units',
                'units as available_units_count' => fn (Builder $query) => $query->where('status', 'available'),
            ])
            ->orderBy('name')
            ->get();

        $projects = $all->map(fn (Project $project): array => [
            'id' => $project->id,
            'name' => $project->name,
            'type' => $project->type,
            'status' => $project->status,
            'location' => $project->location,
            'units_count' => (int) $project->getAttribute('units_count'),
            'available_units_count' => (int) $project->getAttribute('available_units_count'),
        ]);

        return Inertia::render('Admin/Projects/Index', ['projects' => $projects]);
    }

    public function show(Request $request, Project $project): Response
    {
        $canViewPrice = $request->user()?->can('units.view-price') ?? false;

        $units = $project->units()
            ->orderBy('number')
            ->get()
            ->map(fn (Unit $unit): array => [
                'id' => $unit->id,
                'number' => $unit->number,
                'type' => $unit->type,
                'size_sqft' => $unit->size_sqft,
                'facing' => $unit->facing,
                'bedrooms' => $unit->bedrooms,
                'status' => $unit->status->value,
                'price' => $canViewPrice
                    ? app(PriceUnitAction::class)->handle($unit)['total']
                    : null,
            ]);

        return Inertia::render('Admin/Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'type' => $project->type,
                'status' => $project->status,
                'location' => $project->location,
                'handover_date' => $project->handover_date?->toDateString(),
            ],
            'units' => $units,
            'canViewPrice' => $canViewPrice,
        ]);
    }
}
